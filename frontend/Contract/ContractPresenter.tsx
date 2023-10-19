'use client'
import { Contract } from './types'
import { Chain } from '@/app/types'
import { Tab } from '@headlessui/react'
import { _InfoList } from './_InfoList'
import { useEffect, useState } from 'react'
import { TabButton } from '../Tabs/TabButton'
import { getContractAddress } from './helpers'
import { AbiRegistry } from '@multiversx/sdk-core'
import { _MainnetDeployer } from './_MainnetDeployer'
import { useChainApi } from '@/app/hooks/useChainApi'
import { _ContractActions } from './_ContractActions'
import { ContractCalls } from './interact/ContractCalls'
import { CommitOverview } from '../Commit/CommitOverview'
import { useAppDispatch, useAppSelector } from '@/app/store'
import { NetworkId, useDidMountEffect } from '@peerme/core-ts'
import { selectNetwork, setNetwork } from '@/app/store-general'
import { ContractOnNetwork, getContractAccount } from './chain'
import { AddressConfigurator } from './address/AddressConfigurator'
import { InitArgsConfigurator } from './configure/InitArgsConfigurator'
import { selectUser } from '../User/selectors'

type Props = {
  project: string
  chain: Chain
  contract: Contract
}

const Networks: NetworkId[] = ['mainnet', 'testnet', 'devnet']

export function ContractPresenter(props: Props) {
  const chainProvider = useChainApi()
  const dispatch = useAppDispatch()
  const appNetwork = useAppSelector(selectNetwork)
  const network = appNetwork || Networks[0]
  const [contractOnNetwork, setContractOnNetwork] =
    useState<ContractOnNetwork | null>(null)
  const [abi, setAbi] = useState<AbiRegistry | null>(null)

  const user = useAppSelector(selectUser)

  useEffect(() => {
    if (!props.contract) return
    const address = getContractAddress(props.contract, props.chain, network)
    if (!address) return
    getContractAccount(chainProvider, address).then(setContractOnNetwork)
  }, [network, props.contract])

  useDidMountEffect(() => {
    setContractOnNetwork(null)
  }, [network])

  useEffect(() => {
    if (!props.contract.abiUrl) return
    fetch(props.contract.abiUrl)
      .then((abiRes) => abiRes.json())
      .then((abi) => setAbi(AbiRegistry.create(abi)))
  }, [props.contract])

  return (
    <div>
      <header className="mb-4">
        <h2>{props.contract.name}</h2>
      </header>
      <_InfoList
        chain={props.chain}
        network={network}
        contract={props.contract}
        contractOnNetwork={contractOnNetwork}
        className="mb-4"
      />
      <Tab.Group
        selectedIndex={Networks.indexOf(network)}
        onChange={(index) => dispatch(setNetwork(Networks[index]))}
      >
        <Tab.List className="flex items-center space-x-2 md:space-x-4 mb-4">
          <TabButton>Mainnet</TabButton>
          <TabButton>Testnet</TabButton>
          <TabButton>Devnet</TabButton>
        </Tab.List>
        <Tab.Panels style={{ minHeight: '80vh' }}>
          <Tab.Panel>
            <AddressConfigurator chain={props.chain} contract={props.contract}>
              <_ContractActions
                chain={props.chain}
                contract={props.contract}
                className="mb-4"
              />
              <_MainnetDeployer
                chain={props.chain}
                network={network}
                contract={props.contract}
                contractOnNetwork={contractOnNetwork}
                abi={abi}
                className="mb-4"
              />
              <ContractCalls
                project={props.project}
                chain={props.chain}
                contract={props.contract}
                abi={abi}
              />
            </AddressConfigurator>
          </Tab.Panel>
          <Tab.Panel>
            <AddressConfigurator chain={props.chain} contract={props.contract}>
              <_ContractActions
                chain={props.chain}
                contract={props.contract}
                className="mb-4"
              />
              <InitArgsConfigurator
                chain={props.chain}
                network={network}
                contract={props.contract}
                abi={abi}
                className="mb-4"
              />
              <ContractCalls
                project={props.project}
                chain={props.chain}
                contract={props.contract}
                abi={abi}
              />
            </AddressConfigurator>
          </Tab.Panel>
          <Tab.Panel>
            <AddressConfigurator chain={props.chain} contract={props.contract}>
              <_ContractActions
                chain={props.chain}
                contract={props.contract}
                className="mb-4"
              />
              <InitArgsConfigurator
                chain={props.chain}
                network={network}
                contract={props.contract}
                abi={abi}
                className="mb-4"
              />
              <ContractCalls
                project={props.project}
                chain={props.chain}
                contract={props.contract}
                abi={abi}
              />
            </AddressConfigurator>
          </Tab.Panel>
        </Tab.Panels>
      </Tab.Group>
    </div>
  )
}
