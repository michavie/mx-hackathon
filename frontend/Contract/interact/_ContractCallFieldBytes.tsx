'use client'
import { useState } from 'react'
import { Input } from '@peerme/web-ui'
import { BytesValue, EndpointParameterDefinition, TypedValue } from '@multiversx/sdk-core'

type Props = {
  definition: EndpointParameterDefinition
  initial?: string
  onChange: (value: TypedValue, raw: any) => void
}

export const _ContractCallFieldBytes = (props: Props) => {
  const [value, setValue] = useState(props.initial || '')

  const handleChange = (val: string) => {
    setValue(val)
    props.onChange(BytesValue.fromUTF8(val), val)
  }

  return (
    <Input
      id={props.definition.name}
      placeholder="..."
      value={value}
      onChange={handleChange}
      className="mb-4"
      autoComplete="off"
      required
    />
  )
}
