'use client'
import clsx from 'clsx'
import { Theme } from '@/app/theme'
import { Switch } from '@peerme/web-ui'
import { useEffect, useState } from 'react'
import { CodeMetadata } from '@multiversx/sdk-core'

type Props = {
  metadata: CodeMetadata
  onChange: (metadata: CodeMetadata) => void
  className?: string
}

export function CodeMetadataConfigurator(props: Props) {
  const metadata = props.metadata.toJSON() as { upgradeable: boolean; readable: boolean; payable: boolean; payableBySc: boolean }
  const [isUpgradable, setIsUpgradable] = useState(metadata.upgradeable)
  const [isReadable, setIsReadable] = useState(metadata.readable)
  const [isPayable, setIsPayable] = useState(metadata.payable)
  const [isPayableBySc, setIsPayableBySc] = useState(metadata.payableBySc)

  useEffect(() => {
    const newMetadata = new CodeMetadata(isUpgradable, isReadable, isPayable, isPayableBySc)
    if (newMetadata.equals(props.metadata)) return
    props.onChange(newMetadata)
  }, [isUpgradable, isReadable, isPayable, isPayableBySc])

  return (
    <section className={clsx('px-4 sm:px-6 py-4', Theme.Background.Subtle, Theme.BorderRadius.Subtle, props.className)}>
      <h3 className="text-lg text-gray-800 dark:text-gray-200 mb-2">Metadata</h3>
      <ul className="grid grid-cols-2 gap-2">
        <li className={clsx('flex px-4 py-2', Theme.Background.Moderate, Theme.BorderRadius.Subtle)}>
          <div className="flex flex-grow items-center">
            <span className="text-xl text-gray-700 dark:text-gray-200">Upgradable</span>
          </div>
          <Switch label="Upgradable" checked={isUpgradable} onChange={(val) => setIsUpgradable(val)} />
        </li>
        <li className={clsx('flex px-4 py-2', Theme.Background.Moderate, Theme.BorderRadius.Subtle)}>
          <div className="flex flex-grow items-center">
            <span className="text-xl text-gray-700 dark:text-gray-200">Readable</span>
          </div>
          <Switch label="Readable" checked={isReadable} onChange={(val) => setIsReadable(val)} />
        </li>
        <li className={clsx('flex px-4 py-2', Theme.Background.Moderate, Theme.BorderRadius.Subtle)}>
          <div className="flex flex-grow items-center">
            <span className="text-xl text-gray-700 dark:text-gray-200">Payable</span>
          </div>
          <Switch label="Payable" checked={isPayable} onChange={(val) => setIsPayable(val)} />
        </li>
        <li className={clsx('flex px-4 py-2', Theme.Background.Moderate, Theme.BorderRadius.Subtle)}>
          <div className="flex flex-grow items-center">
            <span className="text-xl text-gray-700 dark:text-gray-200">Payable by Contract</span>
          </div>
          <Switch label="Payable by SC" checked={isPayableBySc} onChange={(val) => setIsPayableBySc(val)} />
        </li>
      </ul>
    </section>
  )
}
