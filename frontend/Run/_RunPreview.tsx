'use client'
import clsx from 'clsx'
import dayjs from 'dayjs'
import Link from 'next/link'
import { Run } from './types'
import { Theme } from '@/app/theme'
import { motion } from 'framer-motion'
import { Animation } from './animation'
import { selectRunOutput } from './store'
import { classNames } from '@peerme/core-ts'
import { useAppSelector } from '@/app/store'
import { useEffect, useMemo, useRef } from 'react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { toGithubCommitUrl, toGithubRepositoryUrl } from '../Integrations/helpers'
import { faCodeBranch, faCodeCommit, faSpinner } from '@fortawesome/pro-solid-svg-icons'
import { toRunDurationDisplay, toRunStatusBackgroundColor, toRunStatusDisplayName } from './helpers'

type Props = {
  run: Run
  pop?: boolean
  className?: string
}

export function _RunPreview(props: Props) {
  const output = useAppSelector(selectRunOutput(props.run.id))
  const outputContainerRef = useRef<HTMLDivElement | null>(null)

  useEffect(() => {
    if (!outputContainerRef.current) return
    outputContainerRef.current.scrollTop = outputContainerRef.current.scrollHeight
  }, [output])

  const gitRepositoryUrl = useMemo(() => {
    if (!props.run.commit || !props.run.contract?.repositories?.github) return null
    return toGithubRepositoryUrl(props.run.contract.repositories.github, props.run.commit.branch)
  }, [props.run])

  const gitCommitUrl = useMemo(() => {
    if (!props.run.commit || !props.run.contract?.repositories?.github) return null
    return toGithubCommitUrl(props.run.contract.repositories.github, props.run.commit.hash)
  }, [props.run])

  return (
    <motion.div
      className={classNames(
        'px-4 py-2 text-gray-600 dark:text-gray-400',
        Theme.Background.Subtle,
        Theme.BorderRadius.Subtle,
        props.className
      )}
      initial="hidden"
      animate="show"
      exit="exit"
      variants={Animation.variants.runPreview}
      layout
    >
      <div className="flex gap-4 ">
        <div className="w-32">
          <div>
            <span className={clsx('inline-block w-2 sm:w-3 h-2 sm:h-3 rounded-full mr-2', toRunStatusBackgroundColor(props.run.status))} />
            <span>{toRunStatusDisplayName(props.run.status)}</span>
          </div>
          {props.run.status !== 'canceled' && (
            <div>
              {!!props.run.duration ? (
                <span>{toRunDurationDisplay(props.run)}</span>
              ) : (
                <FontAwesomeIcon icon={faSpinner} className="animate-spin" />
              )}
            </div>
          )}
        </div>
        <div className="flex-grow">
          {!!props.run.commit && (
            <div className="w-40 sm:w-48">
              <Link href={gitRepositoryUrl || '#'} target="_blank" rel="noopener" className="block">
                <FontAwesomeIcon icon={faCodeBranch} className="inline-block mr-2" />
                <span>{props.run.commit.branch}</span>
              </Link>
              <Link href={gitCommitUrl || '#'} target="_blank" rel="noopener" className="block">
                <FontAwesomeIcon icon={faCodeCommit} className="inline-block mr-2" />
                <span>{props.run.commit.hash.substring(0, 7)}</span>
              </Link>
            </div>
          )}
        </div>
        <div>{dayjs(props.run.createdAt).fromNow()}</div>
      </div>
      {!!output && (
        <code
          ref={outputContainerRef}
          className="block px-4 py-2 whitespace-pre text-sm text-white bg-black rounded-xl mt-4 h-64 overflow-y-scroll"
        >
          {output}
        </code>
      )}
    </motion.div>
  )
}
