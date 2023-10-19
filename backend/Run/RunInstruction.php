<?php

namespace App\Domain\Run;

use App\Domain\Contract\ContractSettingsKey;
use App\Domain\Run\Models\Run;
use App\Domain\SocialPlatform;

class RunInstruction
{
    public function __construct(
        public Run $run,
        public array $userSteps,
    ) {
    }

    public function getSteps(): array
    {
        if ($this->run->contract_id) {
            return [
                'rm -rf /app/output',
                'mkdir -p /app/output',
                'python /multiversx_sdk_rust_contract_builder/main.py --output=/app/output --cargo-target-dir=/rust/cargo-target-dir --project=/app',
                'mkdir -p /runner-artifacts && cp -r /app/output/* /runner-artifacts/',
                ...$this->userSteps,
            ];
        }

        return $this->userSteps;
    }

    public function getGithubRepoName(): ?string
    {
        if ($this->run->contract_id) {
            return $this->run->contract->settings()->get(ContractSettingsKey::GithubRepository->value);
        }

        return null;
    }

    public function getGithubOAuthToken(): ?string
    {
        return $this->run->project->user
            ->getSocialAccount(SocialPlatform::Github)
            ?->oauthAccessToken;
    }

    public function getRunnerVersionLatest(): int
    {
        return 010; // TODO: fetch latest version from github
    }
}
