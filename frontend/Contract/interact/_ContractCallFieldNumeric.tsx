'use client'
import { useState } from 'react'
import { Input } from '@peerme/web-ui'
import { sanitizeNumeric } from '@peerme/core-ts'
import { EndpointParameterDefinition, NumericalValue, TypedValue } from '@multiversx/sdk-core'

type Props = {
  definition: EndpointParameterDefinition
  initial?: string
  onChange: (value: TypedValue, raw: any) => void
}

export const _ContractCallFieldNumeric = (props: Props) => {
  const [value, setValue] = useState(props.initial || '')

  const handleChange = (val: string) => {
    setValue(val)
    props.onChange(new NumericalValue(props.definition.type as any, +sanitizeNumeric(val)), val)
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
