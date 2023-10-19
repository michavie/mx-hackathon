import { Contract } from '../Contract/types'
import { IHttpService } from '@peerme/core-ts'
import { GithubOrganization, GithubRepository } from './types'

export const getGithubOrganizationsRequest = async (http: IHttpService) =>
  await http.get<GithubOrganization[]>('integrations/github/organizations')

export const getGithubRepositoriesRequest = async (http: IHttpService, organization: string, page = 1) =>
  await http.get<GithubRepository[]>(`integrations/github/repositories?organization=${organization}&page=${page}`)

export const storeGithubLinkRequest = async (http: IHttpService, repository: string, contractId: string) =>
  await http.post<Contract>('integrations/github/link', { repository, contract: contractId })

export const storeGithubUnlinkRequest = async (http: IHttpService, contractId: string) =>
  await http.post<Contract>('integrations/github/unlink', { contract: contractId })
