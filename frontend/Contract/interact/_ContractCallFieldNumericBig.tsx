'use client'
import { useState } from 'react'
import BigNumber from 'bignumber.js'
import { Input, showToast } from '@peerme/web-ui'
import { classNames, sanitizeNumeric, useDidMountEffect } from '@peerme/core-ts'
import { BigUIntValue, EndpointParameterDefinition, TypedValue } from '@multiversx/sdk-core'

type Props = {
  definition: EndpointParameterDefinition
  initial?: string
  onChange: (value: TypedValue, raw: any) => void
}

export const _ContractCallFieldNumericBig = (props: Props) => {
  const [value, setValue] = useState(props.initial || '')
  const [valueRaw, setValueRaw] = useState('')
  const [decimals, setDecimals] = useState<string | null>(null)
  const govTokenDecimals = 0

  useDidMountEffect(() => {
    if (decimals && +decimals > 18) {
      showToast('Decimals must not exceed 18', 'error')
      return
    }
    const safeDecimals = parseInt(decimals || '0')
    const shifted = new BigNumber(value).shiftedBy(safeDecimals)
    setValueRaw(shifted.toFixed())
  }, [value, decimals])

  useDidMountEffect(() => {
    const safeValue = sanitizeNumeric(valueRaw) || 0
    props.onChange(new BigUIntValue(safeValue), safeValue)
  }, [valueRaw])

  return (
    <div className="mb-4">
      <div className="flex flex-wrap md:flex-nowrap md:space-x-2 mb-2 md:mb-0">
        <Input
          id={props.definition.name}
          placeholder="..."
          value={value}
          onChange={(val) => setValue(sanitizeNumeric(val))}
          className={classNames('w-full mb-2 md:mb-0', govTokenDecimals === 0 ? 'md:w-full' : 'md:w-1/2')}
          autoComplete="off"
          required
        />
        <div className={classNames('flex space-x-2 w-full', govTokenDecimals === 0 ? 'hidden' : 'md:w-1/2')}>
          {govTokenDecimals !== 0 && (
            <OptionButton onClick={() => setDecimals(govTokenDecimals)} decimals={govTokenDecimals} active={govTokenDecimals === decimals}>
              Vote Token
            </OptionButton>
          )}
          {govTokenDecimals !== 0 && (
            <OptionButton onClick={() => setDecimals(null)} active={decimals === null}>
              Custom
            </OptionButton>
          )}
        </div>
      </div>
      {!!valueRaw && decimals !== null && +decimals !== 0 && (
        <p className="text-base text-gray-500 pl-2">
          Formatted for Smart Contract: <strong>{valueRaw}</strong>
        </p>
      )}
    </div>
  )
}

const OptionButton = (props: { onClick: () => void; decimals?: number; children: any; active: boolean }) => (
  <button
    type="button"
    onClick={props.onClick}
    className={classNames(
      'flex-1 block text-white text-left text-sm rounded-xl px-4 py-2 transition duration-300',
      props.active ? 'bg-primary-500' : 'bg-gray-800'
    )}
  >
    <span className="whitespace-no-wrap">{props.children}</span>
    {props.decimals !== undefined && <span className="block text-xs text-gray-200">{props.decimals} decimals</span>}
  </button>
)
