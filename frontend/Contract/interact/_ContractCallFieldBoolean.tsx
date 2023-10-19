'use client'
import { Switch } from '@peerme/web-ui'
import { useEffect, useState } from 'react'
import { BooleanValue, EndpointParameterDefinition, TypedValue } from '@multiversx/sdk-core'

type Props = {
  definition: EndpointParameterDefinition
  initial?: boolean
  onChange: (value: TypedValue, raw: any) => void
}

export const _ContractCallFieldBoolean = (props: Props) => {
  const [value, setValue] = useState(props.initial || false)

  useEffect(() => {
    props.onChange(new BooleanValue(value), value)
  }, [value])

  return (
    <div className="mb-4 pl-2">
      <Switch label="..." checked={value} onChange={(val) => setValue(val)} />
    </div>
  )
}
