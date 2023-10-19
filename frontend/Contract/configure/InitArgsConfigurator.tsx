'use client'
import clsx from 'clsx'
import { Theme } from '@/app/theme'
import { useEffect, useState } from 'react'
import { Chain, ChainNetwork } from '@/app/types'
import { AbiRegistry } from '@multiversx/sdk-core'
import { handleAppResponse } from '@peerme/web-ui'
import { useAppHttp } from '@/app/hooks/useAppHttp'
import { storeContractInitArgsRequest } from '../api'
import { Contract, SerializableArgument } from '../types'
import { ContractCallField } from '../interact/ContractCallField'
import { getContractInitArgs } from '../helpers'

type Props = {
  chain: Chain
  network: ChainNetwork
  contract: Contract
  abi?: AbiRegistry | null
  className?: string
  onArgsChange?: (args: SerializableArgument[]) => void
}

export function InitArgsConfigurator(props: Props) {
  const http = useAppHttp()
  const [args, setArgs] = useState<SerializableArgument[][]>([])
  const [abi, setAbi] = useState<AbiRegistry | null>(props.abi || null)

  useEffect(() => {
    if (!!abi || !props.contract.abiUrl) return
    fetch(props.contract.abiUrl)
      .then((abiRes) => abiRes.json())
      .then((abi) => setAbi(AbiRegistry.create(abi)))
  }, [props.contract])

  useEffect(() => {
    if (!args.length) return
    const initArgs = args.flat(2)
    console.log('initArgs', initArgs)
    props.onArgsChange?.(initArgs)
    handleAppResponse(storeContractInitArgsRequest(http, props.contract.slug, props.network, initArgs), () => {})
  }, [args])

  const handleArgsChange = (_: SerializableArgument[], index: number, raw: SerializableArgument[]) =>
    setArgs((args) => {
      const newArgs = [...args]
      newArgs[index] = raw
      return newArgs
    })

  if (!abi || !abi.constructorDefinition || abi.constructorDefinition.input.length === 0) {
    return null
  }

  return (
    <section className={clsx('px-4 sm:px-6 pt-4 pb-2', Theme.Background.Subtle, Theme.BorderRadius.Subtle, props.className)}>
      <div key={abi.constructorDefinition.name}>
        <h3 className="text-lg text-gray-800 dark:text-gray-200 mb-2">Initial Arguments</h3>
        {abi.constructorDefinition.input.map((paramDefinition, index) => (
          <ContractCallField
            key={index}
            index={index}
            definition={paramDefinition}
            initial={getContractInitArgs(props.contract, props.chain, props.network)?.[index]}
            onChange={handleArgsChange}
          />
        ))}
      </div>
    </section>
  )
}
