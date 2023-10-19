<?php

namespace App\Domain\Contract\Actions;

use App\Domain\Contract\ContractSettingsKey;
use App\Domain\Contract\Models\Contract;
use App\Domain\User\Models\User;
use InvalidArgumentException;

class UnlinkGitRepositoryAction
{
    public function execute(User $user, Contract $contract): void
    {
        throw_unless($user, InvalidArgumentException::class, 'user is required');
        throw_unless($contract, InvalidArgumentException::class, 'contract is required');
        throw_unless($user->owns($contract->project), InvalidArgumentException::class, 'user does not own project');

        $contract->settings()->deleteMultiple([
            ContractSettingsKey::RepositoryLinkedAt->value,
            ContractSettingsKey::GithubRepository->value,
        ]);
    }
}
