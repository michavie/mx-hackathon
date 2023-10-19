'use client'
import clsx from 'clsx'
import dayjs from 'dayjs'
import Link from 'next/link'
import { Theme } from '@/app/theme'
import { Config } from '@/app/config'
import { Contract } from '../Contract/types'
import { GithubOrganization } from './types'
import { InfiniteScroll } from '../InfiniteScroll'
import { useAppHttp } from '@/app/hooks/useAppHttp'
import { useEffect, useMemo, useState } from 'react'
import { hasContractAddresses } from '../Contract/helpers'
import { faGithub } from '@fortawesome/free-brands-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { Alert, Button, Input, Select, SelectOption, handleAppResponse, showToast } from '@peerme/web-ui'
import { getGithubOrganizationsRequest, getGithubRepositoriesRequest, storeGithubLinkRequest, storeGithubUnlinkRequest } from './api'

type GitProvider = 'github'
type GitProviderOrNone = GitProvider | 'none'

type Props = {
  contract: Contract
  provider?: GitProviderOrNone
  title?: string
  descriptionConnected?: string
  className?: string
  onConnected?: () => void
}

export function GitConnector(props: Props) {
  const http = useAppHttp()
  const [contract, setContract] = useState(props.contract)
  const [provider, setProvider] = useState<GitProviderOrNone>(props.provider || 'none')
  const [organizations, setOrganizations] = useState<GithubOrganization[]>([])
  const [selectedOrganization, setSelectedOrganization] = useState<string | null>(null)
  const [searchQuery, setSearchQuery] = useState('')
  const [isLoading, setIsLoading] = useState(true)
  const organizationOptions = useMemo(() => toSelectOptions(organizations), [organizations])

  useEffect(() => {
    if (contract.linkedAt) return
    handleAppResponse(
      getGithubOrganizationsRequest(http),
      (data) => {
        setIsLoading(false)
        setOrganizations(data)
        if (data.length) setSelectedOrganization(data[0].name)
      },
      () => setIsLoading(false)
    )
  }, [contract])

  const handleLink = (repository: string) =>
    handleAppResponse(storeGithubLinkRequest(http, repository, contract.slug), (data) => {
      setContract(data)
      setSelectedOrganization(null)
      showToast('Successfully linked', 'success')
      props.onConnected?.()
    })

  const handleUnlink = () =>
    handleAppResponse(storeGithubUnlinkRequest(http, contract.slug), (data) => {
      setContract(data)
      showToast('Successfully unlinked', 'success')
    })

  return isLoading ? (
    <section className={clsx('px-4 sm:px-6 py-2 sm:py-4', Theme.Background.Subtle, Theme.BorderRadius.Subtle, props.className)}>
      <p>Loading ...</p>
    </section>
  ) : (
    <section className={clsx('px-4 sm:px-6 py-2 sm:py-4', Theme.Background.Subtle, Theme.BorderRadius.Subtle, props.className)}>
      {contract.linkedAt ? (
        <div>
          <h3>{props.title || 'Connected GitHub Repository'}</h3>
          <p className="mb-4">{props.descriptionConnected || 'Automatic builds are enabled for this repository.'}</p>
          <div
            className={clsx(
              'flex items-center gap-4 px-4 sm:px-6 py-2 sm:py-4',
              Theme.Background.Moderate,
              Theme.BorderRadius.Subtle,
              props.className
            )}
          >
            <FontAwesomeIcon icon={faGithub} className="text-4xl text-white" />
            <p className="flex-grow dark:text-gray-200">
              {contract.repositories.github}
              <br />
              Connected {dayjs(contract.linkedAt).fromNow()}
            </p>
            <div className="flex justify-end items-center">
              <Button onClick={handleUnlink} color="red" className="w-full" inverted>
                Unlink
              </Button>
            </div>
          </div>
        </div>
      ) : selectedOrganization ? (
        <div>
          <header className="mb-4">
            <h3>{props.title || 'Connect GitHub Repository'}</h3>
            <p>Enable automatic smart contract deployments by connecting this project to GitHub.</p>
            {provider !== 'none' && hasContractAddresses(props.contract) && (
              <Alert type="warning" className="mt-2">
                Existing <strong>devnet</strong> addresses will be reset when linking to a new repository.
              </Alert>
            )}
          </header>
          {provider === 'none' ? (
            <ul>
              <li>
                <button
                  onClick={() => setProvider('github')}
                  className="bg-black px-4 py-3 rounded-xl transition duration-300 hover:opacity-80"
                >
                  <FontAwesomeIcon icon={faGithub} className="inline-block mr-2 text-2xl text-white" />
                  <span className="text-lg text-white">Connect GitHub Repository</span>
                </button>
              </li>
            </ul>
          ) : provider === 'github' ? (
            <div>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-2 mb-2">
                <Select options={organizationOptions} onSelect={(val) => setSelectedOrganization(val)} />
                <Input placeholder="Search ..." value={searchQuery} onChange={(val) => setSearchQuery(val)} />
              </div>
              <InfiniteScroll
                onLoadItems={(page) => getGithubRepositoriesRequest(http, selectedOrganization, page)}
                refreshObserver={selectedOrganization}
                content={(repos, loading) => {
                  if (loading) return <p>Loading ...</p>
                  return repos
                    .filter((r) => searchQuery.length < 1 || r.name.toLowerCase().includes(searchQuery.toLowerCase()))
                    .map((repo) => (
                      <button
                        key={repo.fullName}
                        onClick={() => handleLink(repo.fullName)}
                        className={clsx(
                          'block w-full px-4 py-2 text-white text-lg text-left mb-2',
                          Theme.Background.ModerateWithHover,
                          Theme.BorderRadius.Subtle
                        )}
                      >
                        {repo.name}
                      </button>
                    ))
                }}
              />
            </div>
          ) : null}
        </div>
      ) : (
        <div>
          <header className="mb-4">
            <h3>{props.title || 'Connect GitHub Repository'}</h3>
            <p>Enable automatic smart contract deployments by connecting this project to GitHub.</p>
            {provider !== 'none' && hasContractAddresses(props.contract) && (
              <Alert type="warning" className="mt-2">
                Existing <strong>devnet</strong> addresses will be reset when linking to a new repository.
              </Alert>
            )}
          </header>
          <ul>
            <li>
              <Link
                href={Config.Urls.SocialAuth('github')}
                className="block bg-black px-4 py-3 rounded-xl transition duration-300 hover:opacity-80"
              >
                <FontAwesomeIcon icon={faGithub} className="inline-block mr-2 text-2xl text-white" />
                <span className="text-lg text-white">Connect GitHub</span>
              </Link>
            </li>
          </ul>
        </div>
      )}
    </section>
  )
}

const toSelectOptions = (organizations: GithubOrganization[]): SelectOption[] =>
  organizations.map((org) => ({
    name: org.name,
    value: org.name,
  }))
