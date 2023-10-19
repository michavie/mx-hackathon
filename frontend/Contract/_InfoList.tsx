'use client'
import { Contract } from './types'
import { Theme } from '@/app/theme'
import { Tooltip } from '@peerme/web-ui'
import { Constants } from '@/app/constants'
import { ContractOnNetwork } from './chain'
import { useAppSelector } from '@/app/store'
import { Chain, ChainNetwork } from '@/app/types'
import { selectNetwork } from '@/app/store-general'
import { faSync } from '@fortawesome/pro-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { getContractAddress, hasContractAddress } from './helpers'
import { capitalizeFirstLetter, classNames, getExplorerUrl, toFormattedTokenAmount } from '@peerme/core-ts'

type Props = {
  chain: Chain
  network: ChainNetwork
  contract: Contract
  contractOnNetwork: ContractOnNetwork | null
  className?: string
}

export function _InfoList(props: Props) {
  const network = useAppSelector(selectNetwork)
  const isConfigured = !!hasContractAddress(props.contract, props.chain, network)
  const address = getContractAddress(props.contract, props.chain, network) || 'Not configured'

  return (
    <ul
      className={classNames(
        'text-gray-800 dark:text-gray-100 px-4 sm:px-6 py-2 sm:py-4',
        Theme.Background.Subtle,
        Theme.BorderRadius.Subtle,
        props.className
      )}
    >
      <li>
        <strong>Address ({capitalizeFirstLetter(network)}): </strong>
        <a href={getExplorerUrl(network, 'accounts/' + address)} target="_blank" rel="noreferrer">
          {address}
        </a>
      </li>
      {isConfigured && !!props.contractOnNetwork && (
        <li>
          <strong>Balance: </strong>
          {toFormattedTokenAmount(props.contractOnNetwork.balance, Constants.Chain.Multiversx.EgldDecimals, 4) + ' EGLD'}
        </li>
      )}
      {isConfigured && !!props.contractOnNetwork && (
        <li>
          <strong>Developer Reward: </strong>
          {toFormattedTokenAmount(props.contractOnNetwork.developerReward, Constants.Chain.Multiversx.EgldDecimals, 4) + ' EGLD'}
        </li>
      )}
      {isConfigured && !!props.contract.linkedAt && (
        <li>
          <Tooltip tip={toAutomaticDeploymentsTooltip(props.network)}>
            <span className="text-green-400">
              <strong>Automatic Deployments are enabled</strong>
              <FontAwesomeIcon icon={faSync} className="ml-2" />
            </span>
          </Tooltip>
        </li>
      )}
    </ul>
  )
}

export const toAutomaticDeploymentsTooltip = (network: ChainNetwork) => {
  if (network === 'devnet') return 'We will automatically deploy your smart contract on Devnet when you push code to GitHub'
  if (network === 'testnet') return 'We will automatically deploy your smart contract on Testnet when you push code to GitHub'
  return 'We will automatically deploy your smart contract on Devnet & Testnet when you push code to GitHub'
}
