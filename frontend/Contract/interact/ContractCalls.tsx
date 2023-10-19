'use client'
import Link from 'next/link'
import { Theme } from '@/app/theme'
import { Chain } from '@/app/types'
import BigNumber from 'bignumber.js'
import { Config } from '@/app/config'
import { useAppSelector } from '@/app/store'
import { getContractAddress } from '../helpers'
import { selectNetwork } from '@/app/store-general'
import { useAppHttp } from '@/app/hooks/useAppHttp'
import { useEffect, useMemo, useState } from 'react'
import { getChainNetworkWallet } from '@/app/helpers'
import { storeContractProxyCallRequest } from '../api'
import { usePendingTx } from '@/app/hooks/usePendingTx'
import { ContractCallField } from './ContractCallField'
import { AuthButton } from '../../Auth/wallet/AuthButton'
import { Contract, SerializableArgument } from '../types'
import { faHourglassHalf } from '@fortawesome/pro-solid-svg-icons'
import { useGetAccountInfo, useGetIsLoggedIn } from '@multiversx/sdk-dapp/hooks'
import { Address, AbiRegistry, EndpointDefinition, TokenTransfer } from '@multiversx/sdk-core'
import { capitalizeFirstLetter, classNames, getExplorerUrl, sanitizeNumeric } from '@peerme/core-ts'
import { Button, Input, Select, SelectOption, TransferSelector, handleAppResponse, showToast } from '@peerme/web-ui'

type Props = {
  project: string
  chain: Chain
  contract: Contract
  abi: AbiRegistry | null
}

export const ContractCalls = (props: Props) => {
  const http = useAppHttp()
  const network = useAppSelector(selectNetwork)
  const { address } = useGetAccountInfo()
  const abiEndpoints = props.abi?.getEndpoints().filter((def) => !def.modifiers.isReadonly())
  const abiEndpointsSelectOptions = useMemo(() => toAbiEndpointSelectOptions(abiEndpoints || []), [abiEndpoints])
  const [activeEndpoint, setActiveEndpoint] = useState<string | null>(null)
  const [activeEndpointDefinition, setActiveEndpointDefinition] = useState<EndpointDefinition | null>(null)
  const [args, setArgs] = useState<SerializableArgument[][]>([])
  const [value, setValue] = useState<BigNumber>(new BigNumber(0))
  const [transfers, setTransfers] = useState<TokenTransfer[]>([])
  const [gasLimit, setGasLimit] = useState('20000000')
  const [isLoading, setIsLoading] = useState(false)
  const isWalletConnected = useGetIsLoggedIn()
  const { account: walletAccount } = useGetAccountInfo()
  const hasSelected = !!activeEndpoint
  const isPayable = !!activeEndpointDefinition && !!activeEndpointDefinition.modifiers?.payableInTokens[0]
  const destination = getContractAddress(props.contract, props.chain, network)
  const shouldProxyExecute = network !== 'mainnet' && !!activeEndpointDefinition?.modifiers.isOnlyOwner()

  const resetAll = () => {
    setActiveEndpoint(null)
    setActiveEndpointDefinition(null)
    setArgs([])
    setValue(new BigNumber(0))
    setTransfers([])
  }

  const resetDetails = () => {
    setArgs([])
    setValue(new BigNumber(0))
    setTransfers([])
  }

  const pendingTx = usePendingTx(
    { Address: destination || '#', Endpoint: activeEndpoint || '#' },
    {
      onSuccess: () => {
        resetAll()
        showToast('Successfully executed', 'success')
      },
    }
  )

  useEffect(() => {
    if (!activeEndpoint || activeEndpoint === 'none') return
    const endpointDefinition = props.abi?.getEndpoint(activeEndpoint)
    if (!endpointDefinition) return
    setActiveEndpointDefinition(endpointDefinition)
  }, [activeEndpoint, props.abi])

  useEffect(() => {
    resetDetails()
  }, [activeEndpoint])

  const handleExecute = async () => {
    if (!activeEndpoint) return
    let interaction = (await pendingTx.createScInteraction(args.flat(2)))
      .withChainID(getChainNetworkWallet(network).ChainId)
      .withSender(new Address(walletAccount.address))
      .withGasLimit(+gasLimit)
      .withNonce(walletAccount.nonce)
      .withValue(value)

    if (transfers.length === 1) {
      const hasNftTransfer = transfers.some((p) => p.nonce !== 0)
      interaction = hasNftTransfer ? interaction.withSingleESDTNFTTransfer(transfers[0]) : interaction.withSingleESDTTransfer(transfers[0])
    } else if (transfers.length > 1) {
      interaction = interaction.withMultiESDTNFTTransfer(transfers)
    }

    console.log('destination', interaction.getContractAddress().bech32())
    console.log('endpoint', activeEndpoint)
    console.log('args', args.flat(2))
    console.log('value', value.toString())
    console.log('transfers', transfers)

    const tx = interaction.buildTransaction()

    if (shouldProxyExecute) {
      const txObject = tx.toPlainObject()
      setIsLoading(true)
      handleAppResponse(
        storeContractProxyCallRequest(http, props.contract.slug, network, txObject),
        (data) => {
          setIsLoading(false)
          showToast('Transaction sent ...', 'success', {
            icon: faHourglassHalf,
            href: getExplorerUrl(network, 'transactions/' + data.tx),
          })
        },
        () => setIsLoading(false),
        1000
      )
    } else {
      await pendingTx.send(tx)
    }
  }

  const handleArgsChange = (val: SerializableArgument[], index: number) =>
    setArgs((args) => {
      const newArgs = [...args]
      newArgs[index] = val
      return newArgs
    })

  const handlePaymentSelected = (payment: TokenTransfer) =>
    payment.isEgld() ? setValue(new BigNumber(payment.amountAsBigInteger)) : setTransfers([payment])

  if (!destination) {
    return null
  }

  return (
    <section className={classNames('px-6 py-4', Theme.Background.Subtle, Theme.BorderRadius.Subtle)}>
      <h3>Interact</h3>
      <p className="mb-4">
        Use the automatically-generated UI to call <strong>{props.contract.name}</strong> functions on{' '}
        <strong>{capitalizeFirstLetter(network)}</strong>.
      </p>
      {!isWalletConnected ? (
        <div className={classNames('p-8 text-center mb-4', Theme.Background.Moderate, Theme.BorderRadius.Subtle)}>
          <h2 className="text-gray-800 dark:text-gray-200 text-xl md:text-2xl mb-4">
            Please <span className="highlight">connect</span> with your MultiversX wallet to use smart contract interactions.
          </h2>
          <AuthButton />
        </div>
      ) : !!abiEndpoints ? (
        <div>
          {abiEndpointsSelectOptions.length > 0 && (
            <Select
              value={activeEndpoint || 'none'}
              options={[{ name: 'None', value: 'none' }, ...abiEndpointsSelectOptions]}
              onSelect={(val) => setActiveEndpoint(val)}
              className="mb-4"
            />
          )}
          {isPayable && (
            <div>
              <h3 className="text-lg text-gray-800 dark:text-gray-200 mb-4">Payment</h3>
              <TransferSelector
                config={getChainNetworkWallet(network)}
                address={address}
                onSelected={handlePaymentSelected}
                className="mb-4"
              />
            </div>
          )}
          {activeEndpointDefinition && activeEndpointDefinition.input.length > 0 && (
            <div key={activeEndpointDefinition.name}>
              <h3 className="text-lg text-gray-800 dark:text-gray-200 mb-4">Arguments</h3>
              {activeEndpointDefinition.input.map((paramDefinition, index) => (
                <ContractCallField key={index} index={index} definition={paramDefinition} onChange={handleArgsChange} />
              ))}
            </div>
          )}
          <label htmlFor="gas" className="inline-block pl-1 text-base sm:text-lg mb-1 text-gray-800 dark:text-gray-200">
            Gas Limit
          </label>
          <Input id="gas" placeholder="Gas limit" value={gasLimit} onChange={(val) => setGasLimit(sanitizeNumeric(val))} className="mb-4" />
          {hasSelected && (
            <Button color="blue" onClick={handleExecute} disabled={isLoading} className="block w-full">
              Execute on {capitalizeFirstLetter(network)}
            </Button>
          )}
          {hasSelected && shouldProxyExecute && (
            <small className="block text-center text-yellow-300 text-base mt-2">
              (This transaction will be executed by the original deployer of the smart contract.)
            </small>
          )}
        </div>
      ) : (
        <div className="p-8 text-center mb-4">
          <p className="font-head text-gray-800 dark:text-gray-200 text-xl md:text-2xl mb-4">
            Please upload the ABI file of the smart contract{' '}
            <Link href={Config.App.Pages.ContractSettings(props.project, props.contract.slug)}>in the settings</Link> to interact with it.
          </p>
        </div>
      )}
    </section>
  )
}

const toAbiEndpointSelectOptions = (definitions: EndpointDefinition[]): SelectOption[] =>
  definitions.map((def) => ({ name: def.modifiers.isOnlyOwner() ? def.name + ' (Only Owner)' : def.name, value: def.name }))
