import { SocialPlatform } from '@peerme/core-ts'
import { Chain, ChainNetworkKey } from '@/app/types'
import { TypedValue } from '@multiversx/sdk-core/out'

export type Contract = {
  slug: string
  name: string
  addresses: {
    [key in Chain]: {
      [network in ChainNetworkKey]?: string
    }
  }
  initArgs: {
    [key in Chain]: {
      [network in ChainNetworkKey]?: any[]
    }
  }
  linkedAt: string | null
  repositories: Record<SocialPlatform, string>
  abiUrl: string | null
}

export type SerializableArgument = TypedValue | string | number
