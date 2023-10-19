'use client'
import { useState } from 'react'
import { storeRunContractRequest } from './api'
import { useAppHttp } from '@/app/hooks/useAppHttp'
import { Button, handleAppResponse, showToast } from '@peerme/web-ui'

type Props = {
  contract?: string
  className?: string
}

export function _RunStarter(props: Props) {
  const http = useAppHttp()
  const [hasStarted, setHasStarted] = useState(false)

  const handleClick = () => {
    if (props.contract) {
      handleAppResponse(storeRunContractRequest(http, props.contract), (data) => {
        setHasStarted(true)
        showToast('Build started', 'success')
      })
    }
  }

  return (
    <Button color="blue" onClick={handleClick} disabled={hasStarted}>
      Start Build
    </Button>
  )
}
