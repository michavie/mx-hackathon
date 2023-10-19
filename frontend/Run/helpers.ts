import { Run, RunStatus } from './types'

export const toRunStatusDisplayName = (status: RunStatus) => {
  if (status === 'pending') return 'Queued'
  if (status === 'active') return 'Running'
  if (status === 'success') return 'Ready'
  if (status === 'failed') return 'Failed'
  if (status === 'canceled') return 'Canceled'
  return 'Unknown'
}

export const toRunStatusBackgroundColor = (status: RunStatus) => {
  if (status === 'pending') return 'bg-gray-400'
  if (status === 'active') return 'bg-orange-400'
  if (status === 'success') return 'bg-green-400'
  if (status === 'failed') return 'bg-red-400'
  if (status === 'canceled') return 'bg-gray-400'
  return 'gray'
}

export const toRunDurationDisplay = (run: Run) => {
  if (run.status === 'pending') return '-'
  return `${run.duration}s`
}
