'use client'
import { useEffect, useState } from 'react'
import { Input, showToast } from '@peerme/web-ui'
import { isValidBlockchainAddress } from '@peerme/core-ts'
import { Address, AddressValue, EndpointParameterDefinition, TypedValue } from '@multiversx/sdk-core'

type Props = {
  definition: EndpointParameterDefinition
  initial?: string
  onChange: (value: TypedValue, raw: any) => void
}

export const _ContractCallFieldAddress = (props: Props) => {
  const [value, setValue] = useState(props.initial || '')

  useEffect(() => {
    if (!value) return
    if (!isValidBlockchainAddress(value)) {
      setValue('')
      showToast('Invalid address format', 'error')
      return
    }
    props.onChange(new AddressValue(new Address(value)), value)
  }, [value])

  return <Input onChange={(val) => setValue(val)} placeholder="..." className="mb-4" />
}
