import { Run } from './types'
import { AppState } from '@/app/store'
import { createSlice, PayloadAction } from '@reduxjs/toolkit'

export type ProjectId = string // TODO: move to project types
export type RunId = string // TODO: move to run types

export type RunState = {
  broadcasted: Record<ProjectId, Run[]>
  output: Record<RunId, string>
}

const initialState: RunState = {
  broadcasted: {},
  output: {},
}

export const slice = createSlice({
  name: 'run',
  initialState,
  reducers: {
    addBroadcastedRun: (state, action: PayloadAction<{ run: Run; output?: string }>) => {
      const run = action.payload.run
      const projectId = run.project?.slug
      if (!projectId) {
        console.warn('broadcasted run is missing project id')
        return
      }
      if (!state.broadcasted[projectId]) {
        state.broadcasted[projectId] = []
      }

      if (state.broadcasted[projectId].some((r) => r.id === run.id)) {
        state.broadcasted[projectId] = state.broadcasted[projectId].map((r) => (r.id === run.id ? run : r))
        state.output[run.id] = state.output[run.id] + '\n' + (action.payload.output || '')
      } else {
        state.broadcasted[projectId] = [run, ...state.broadcasted[projectId]]
        state.output[run.id] = action.payload.output || ''
      }
    },
    removeBroadcastedRun: (state, action: PayloadAction<Run>) => {
      const projectId = action.payload.project?.slug
      if (!projectId) {
        console.warn('broadcasted run is missing project id')
        return
      }
      if (!state.broadcasted[projectId]) {
        state.broadcasted[projectId] = []
      }

      state.broadcasted[projectId] = state.broadcasted[projectId].filter((run) => run.id !== action.payload.id)
    },
  },
})

export const { addBroadcastedRun, removeBroadcastedRun } = slice.actions

export const runReducer = slice.reducer

// selectors
export const selectBroadcastedRuns = (state: AppState) => Object.values(state.run.broadcasted).flat()

export const selectProjectRunsBroadcasted = (projectId?: ProjectId) => (state: AppState) =>
  projectId ? state.run.broadcasted[projectId] || [] : []

export const selectRunOutput = (runId?: RunId) => (state: AppState) => runId ? state.run.output[runId] || '' : ''
