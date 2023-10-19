'use client'
import { getRunsRequest } from './api'
import { _RunPreview } from './_RunPreview'
import { _RunStarter } from './_RunStarter'
import { useAppSelector } from '@/app/store'
import { AnimatePresence } from 'framer-motion'
import { InfiniteScroll } from '../InfiniteScroll'
import { useAppHttp } from '@/app/hooks/useAppHttp'
import { selectProjectRunsBroadcasted } from './store'

type Props = {
  projectId: string
  contractId?: string
  elementId?: string
  className?: string
}

export function RunOverview(props: Props) {
  const http = useAppHttp()
  const broadcastedByProject = useAppSelector(selectProjectRunsBroadcasted(props.projectId))
  const broadcasted = props.elementId
    ? broadcastedByProject.filter((event) => event.blueprint?.uuid === props.elementId)
    : broadcastedByProject

  return (
    <div className={props.className}>
      <header className="flex justify-between items-center mb-4">
        <h2>Builds</h2>
        <_RunStarter contract={props.contractId} />
      </header>
      <AnimatePresence>
        {broadcasted.map((run, i) => (
          <_RunPreview key={run.id} run={run} pop={i === 0} className="mb-2" />
        ))}
      </AnimatePresence>
      <InfiniteScroll
        onLoadItems={(page) => getRunsRequest(http, { contractId: props.contractId, elementId: props.elementId }, page)}
        content={(runs, isLoading) => {
          if (isLoading) return <p>Loading...</p>
          if (!runs) return <p>No events yet.</p>
          return runs
            .filter((run) => !broadcasted.some((b) => b.id === run.id)) // hide currently broadcasted
            .map((run) => <_RunPreview key={run.id} run={run} className="mb-2" />)
        }}
      />
    </div>
  )
}
