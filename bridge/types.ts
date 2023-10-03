import { ChainNetwork } from 'src/types'

export type DeployRequest = {
  network: ChainNetwork
  contract: {
    address: string | null
    initArgs: any[]
  }
  artifact: {
    url: string
  }
}
