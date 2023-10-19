'use client'
import { useState } from 'react'
import { Contract } from './types'
import { Chain } from '@/app/types'
import { Config } from '@/app/config'
import { useRouter } from 'next/navigation'
import { GitConnector } from '../Integrations/GitConnector'
import { AddressConfigurator } from './address/AddressConfigurator'

type Props = {
  project: string
  chain: Chain
  contract: Contract
}

export function ContractSetup(props: Props) {
  const router = useRouter()

  const handleDone = () => router.push(Config.App.Pages.Contract(props.project, props.contract.slug))

  return (
    <div>
      <GitConnector
        contract={props.contract}
        provider="github"
        title="Connect your GitHub Repository (recommended)"
        descriptionConnected="When you push your code to this repository, we will automatically build and deploy your smart contract to Devnet and Testnet."
        className="mb-4"
        onConnected={handleDone}
      />
      <AddressConfigurator
        chain="mx"
        contract={props.contract}
        title="Or manually set a Smart Contract address"
        className="opacity-80"
        onConfigured={handleDone}
      />
    </div>
  )
}
