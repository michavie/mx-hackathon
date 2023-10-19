'use client'
import { useRouter } from 'next/navigation'
import { storeContractRequest } from './api'
import { SyntheticEvent, useState } from 'react'
import { useAppHttp } from '@/app/hooks/useAppHttp'
import { Button, Input, StickyModal, handleAppResponse } from '@peerme/web-ui'
import { Config } from '@/app/config'

type Props = {
  projectId: string
}

export default function ContractCreator(props: Props) {
  const http = useAppHttp()
  const router = useRouter()
  const [isOpen, setIsOpen] = useState(false)
  const [name, setName] = useState('')

  const handleSubmit = (e: SyntheticEvent) => {
    e.preventDefault()
    handleAppResponse(storeContractRequest(http, props.projectId, name), (data) => {
      setIsOpen(false)
      setName('')
      router.push(Config.App.Pages.ContractSetup(props.projectId, data.slug))
    })
  }

  return (
    <>
      <Button onClick={() => setIsOpen(true)} color="primary">
        New Contract
      </Button>
      <StickyModal open={isOpen} onClose={() => setIsOpen(false)}>
        <h2 className="mb-4">Let's spawn a new smart contract.</h2>
        <form onSubmit={handleSubmit}>
          <label htmlFor="name" className="pl-1 text-xl mb-2 text-gray-800 dark:text-gray-200">
            Contract Name
          </label>
          <Input id="name" placeholder="Awesome Smart Contract" value={name} onChange={(val) => setName(val)} autoFocus />
          <Button color="blue" className="block w-full mt-4" submit>
            Create Contract
          </Button>
        </form>
      </StickyModal>
    </>
  )
}
