import { Contract } from './types'
import { toChainNetworkKey } from '@/app/helpers'
import { Chain, ChainNetwork } from '@/app/types'

export const hasContractAddresses = (contract: Contract) => Object.keys(contract.addresses).length > 0

export const hasContractAddress = (contract: Contract, chain: Chain, network: ChainNetwork) =>
  !!contract.addresses[chain]?.[toChainNetworkKey(network)]

export const getContractAddress = (contract: Contract, chain: Chain, network: ChainNetwork) =>
  contract.addresses[chain]?.[toChainNetworkKey(network)] || null

export const getContractInitArgs = (contract: Contract, chain: Chain, network: ChainNetwork) =>
  contract.initArgs[chain]?.[toChainNetworkKey(network)] || null
