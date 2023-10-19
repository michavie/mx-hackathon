'use client'
import { useMemo } from 'react'
import { SerializableArgument } from '../types'
import { capitalizeFirstLetter } from '@peerme/core-ts'
import { _ContractCallFieldBytes } from './_ContractCallFieldBytes'
import { _ContractCallFieldNumeric } from './_ContractCallFieldNumeric'
import { _ContractCallFieldAddress } from './_ContractCallFieldAddress'
import { _ContractCallFieldBoolean } from './_ContractCallFieldBoolean'
import { _ContractCallFieldVariadic } from './_ContractCallFieldVariadic'
import { _ContractCallFieldNumericBig } from './_ContractCallFieldNumericBig'
import {
  BytesType,
  AddressType,
  BigUIntType,
  BooleanType,
  VariadicType,
  NumericalType,
  EndpointParameterDefinition,
} from '@multiversx/sdk-core'

type Props = {
  index: number
  definition: EndpointParameterDefinition
  initial?: string | boolean
  onChange: (args: SerializableArgument[], index: number, raw: SerializableArgument[]) => void
}

export const ContractCallField = (props: Props) => {
  const name = useMemo(() => toDisplayName(props.definition.name), [props.definition.name])

  return (
    <div>
      <label htmlFor={props.definition.name} className="pl-1 text-base sm:text-lg mb-2 text-gray-800 dark:text-gray-200">
        {name} <small className="opacity-70 text-base">({props.definition.type.getName()})</small>
      </label>
      {props.definition.type.hasClassOrSuperclass(BytesType.ClassName) ? (
        <_ContractCallFieldBytes
          initial={props.initial as string}
          definition={props.definition}
          onChange={(val, raw) => props.onChange([val], props.index, [raw])}
        />
      ) : props.definition.type.hasClassOrSuperclass(BigUIntType.ClassName) ? (
        <_ContractCallFieldNumericBig
          initial={props.initial as string}
          definition={props.definition}
          onChange={(val, raw) => props.onChange([val], props.index, [raw])}
        />
      ) : props.definition.type.hasClassOrSuperclass(BooleanType.ClassName) ? (
        <_ContractCallFieldBoolean
          initial={props.initial as boolean}
          definition={props.definition}
          onChange={(val, raw) => props.onChange([val], props.index, [raw])}
        />
      ) : props.definition.type.hasClassOrSuperclass(NumericalType.ClassName) ? (
        <_ContractCallFieldNumeric
          initial={props.initial as string}
          definition={props.definition}
          onChange={(val, raw) => props.onChange([val], props.index, [raw])}
        />
      ) : props.definition.type.hasClassOrSuperclass(AddressType.ClassName) ? (
        <_ContractCallFieldAddress
          initial={props.initial as string}
          definition={props.definition}
          onChange={(val, raw) => props.onChange([val], props.index, [raw])}
        />
      ) : props.definition.type.hasClassOrSuperclass(VariadicType.ClassName) ? (
        <_ContractCallFieldVariadic definition={props.definition} onChange={(val, raw) => props.onChange(val, props.index, raw)} />
      ) : (
        <_ContractCallFieldBytes
          initial={props.initial as string}
          definition={props.definition}
          onChange={(val, raw) => props.onChange([val], props.index, [raw])}
        />
      )}
    </div>
  )
}

const toDisplayName = (val: string) =>
  val
    .replaceAll('_', ' ')
    .split(' ')
    .map((x) => capitalizeFirstLetter(x))
    .join(' ')
