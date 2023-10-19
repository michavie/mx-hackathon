import { Commit } from '../Commit/types'
import { Project } from '../Project/types'
import { Blueprint } from '../Spawn/types'
import { Contract } from '../Contract/types'

export type RunStatus = 'pending' | 'active' | 'success' | 'failed' | 'canceled'

export type Run = {
  id: string
  status: RunStatus
  project: Project
  contract: Contract | null
  blueprint: Blueprint | null
  commit: Commit | null
  duration: number
  meta: {
    tx: string | null
    codeHash: string | null
  }
  startedAt: string
  createdAt: string
}

export type RunSettings = {
  rootDir: string | null
}
