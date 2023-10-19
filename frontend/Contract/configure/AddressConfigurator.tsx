'use client'
import clsx from 'clsx'
import { Contract } from '../types'
import { Theme } from '@/app/theme'
import { Chain } from '@/app/types'
import { useRouter } from 'next/navigation'
import { useAppSelector } from '@/app/store'
import { hasContractAddress } from '../helpers'
import { SyntheticEvent, useState } from 'react'
import { selectNetwork } from '@/app/store-general'
import { useAppHttp } from '@/app/hooks/useAppHttp'
import { storeContractAddressRequest } from '../api'
import { Button, Input, handleAppResponse, showToast } from '@peerme/web-ui'
import { capitalizeFirstLetter, isValidBlockchainAddress } from '@peerme/core-ts'

type Props = {
  chain: Chain
  contract: Contract
  children?: React.ReactNode
  title?: string
  className?: string
  onConfigured?: () => void
}

export function AddressConfigurator(props: Props) {
  const http = useAppHttp()
  const network = useAppSelector(selectNetwork)
  const router = useRouter()
  const [address, setAddress] = useState('')
  const [isLoading, setIsLoading] = useState(false)
  const hasAddressForNetwork = hasContractAddress(props.contract, props.chain, network)

  if (hasAddressForNetwork || !!props.contract.linkedAt) {
    return <div className={props.className}>{props.children}</div>
  }

  const handleSubmit = (e: SyntheticEvent) => {
    e.preventDefault()
    if (!isValidBlockchainAddress(address)) {
      showToast('Not a valid address', 'error')
      return
    }
    setIsLoading(true)
    handleAppResponse(
      storeContractAddressRequest(http, props.contract.slug, network, address),
      () => {
        showToast(`Configured ${capitalizeFirstLetter(network)}`, 'success')
        setIsLoading(false)
        if (props.onConfigured) {
          props.onConfigured()
        } else {
          router.refresh()
        }
      },
      () => setIsLoading(false)
    )
  }

  return (
    <section className={clsx('px-4 sm:px-6 py-2 sm:py-4', Theme.Background.Subtle, Theme.BorderRadius.Subtle, props.className)}>
      <header>
        <h3>
          {props.title || (
            <span>
              Configure your Smart Contract for <strong className="highlight">{capitalizeFirstLetter(network)}</strong>
            </span>
          )}
        </h3>
        <p className="mb-4">
          Set the <strong>{capitalizeFirstLetter(network)}</strong> address of your Smart Contract to enable further utilities.
        </p>
      </header>
      <form onSubmit={handleSubmit} className="flex gap-4">
        <Input placeholder="Smart contract address ..." onChange={(val) => setAddress(val)} />
        <Button color="blue" loading={isLoading} submit>
          Configure
        </Button>
      </form>
    </section>
  )
}
