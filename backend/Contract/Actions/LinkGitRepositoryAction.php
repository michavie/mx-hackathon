<?php

namespace App\Domain\Contract\Actions;

use App\Domain\Chain;
use App\Domain\ChainNetwork;
use App\Domain\Contract\ContractSettingsKey;
use App\Domain\Contract\Models\Contract;
use App\Domain\Integration\SourceControlProvider;
use App\Domain\Run\RunStatus;
use App\Domain\User\Models\User;
use InvalidArgumentException;

class LinkGitRepositoryAction
{
    public function execute(User $user, Contract $contract, SourceControlProvider $provider, string $repository): void
    {
        throw_unless($user, InvalidArgumentException::class, 'user is required');
        throw_unless($contract, InvalidArgumentException::class, 'contract is required');
        throw_unless($repository, InvalidArgumentException::class, 'repository is required');
        throw_unless($user->owns($contract->project), InvalidArgumentException::class, 'user does not own project');

        $this->unsetDevnetAddresses($contract);

        match ($provider) {
            SourceControlProvider::Github => $contract->settings()->set(ContractSettingsKey::GithubRepository->value, $repository),
            default => throw new InvalidArgumentException('provider is not supported'),
        };

        $contract->settings()->set(ContractSettingsKey::RepositoryLinkedAt->value, now()->toIso8601String());

        $this->startRun($contract);
    }

    private function unsetDevnetAddresses(Contract $contract): void
    {
        $contract->unsetAddress(Chain::Multiversx, ChainNetwork::Devnet);
    }

    private function startRun(Contract $contract): void
    {
        $contract->project->runs()->create([
            'status' => RunStatus::Pending,
            'contract_id' => $contract->id,
            'blueprint_id' => null,
        ]);
    }
}
