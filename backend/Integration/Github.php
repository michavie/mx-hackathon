<?php

namespace App\Domain\Integration;

use App\Domain\SocialPlatform;
use App\Domain\User\Models\User;
use Github\Client;
use GrahamCampbell\GitHub\GitHubFactory;
use Illuminate\Support\Collection;

class Github
{
    public function __construct(
        private GitHubFactory $factory,
    ) {
    }

    public function organizations(User $user): Collection
    {
        return collect($this->getConfiguredUserClient($user)
            ->me()
            ->organizations())
            ->map(fn ($org) => new GithubOrganization(
                name: $org['login'],
                avatarUrl: $org['avatar_url'],
            ));
    }

    public function repositories(User $user, string $organization, int $page = 1): Collection
    {
        return collect($this->getConfiguredUserClient($user)
            ->organizations()
            ->repositories($organization, 'all', $page))
            ->map(fn ($repo) => new GithubRepository(
                id: $repo['id'],
                name: $repo['name'],
                fullName: $repo['full_name'],
                private: $repo['private']
            ));
    }

    public function getConfiguredUserClient(User $user): Client
    {
        $token = $user->getSocialAccount(SocialPlatform::Github)->oauthAccessToken;

        return $this->factory->make([
            'token' => $token,
            'method' => 'token',
            'cache' => false,
            'backoff' => true,
        ]);
    }
}
