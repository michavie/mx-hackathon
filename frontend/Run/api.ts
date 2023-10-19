import { IHttpService } from '@/app/http'
import { Run, RunSettings } from './types'

export const getRunsRequest = async (http: IHttpService, options: { contractId?: string; elementId?: string }, page = 1) => {
  let endpoint = ''
  if (options.contractId) {
    endpoint = `runs?contract=${options.contractId}&page=${page}`
  } else if (options.elementId) {
    endpoint = `runs?element=${options.elementId}&page=${page}`
  }

  return await http.get<Run[]>(endpoint)
}

export const storeRunContractRequest = async (http: IHttpService, contractId: string) => await http.post(`runs?contract=${contractId}`)

export const getRunContractSettingsRequest = async (http: IHttpService, contractId: string) =>
  await http.get<RunSettings>(`runs/settings?contract=${contractId}`)

export const storeRunContractSettingsRequest = async (http: IHttpService, contractId: string, settings: RunSettings) =>
  await http.post(`runs/settings?contract=${contractId}`, settings)
