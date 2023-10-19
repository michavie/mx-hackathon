const GithubBaseUrl = 'https://github.com'

export const toGithubRepositoryUrl = (repository: string, branch?: string) => {
  if (branch) return `${GithubBaseUrl}/${repository}/tree/${branch}`
  return `${GithubBaseUrl}/${repository}`
}

export const toGithubCommitUrl = (repository: string, hash: string) => `${GithubBaseUrl}/${repository}/commit/${hash}`
