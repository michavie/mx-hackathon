<?php

use App\Domain\Contract\Actions\UnlinkGitRepositoryAction;
use App\Domain\Contract\ContractSettingsKey;
use App\Domain\Contract\Models\Contract;
use App\Domain\Project\Models\Project;
use App\Domain\User\Models\User;

it('unlinks a contract from git repositories', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $contract = Contract::factory()->for($project)->create();

    $contract->settings()->set(ContractSettingsKey::GithubRepository->value, 'org/some-repo');

    (new UnlinkGitRepositoryAction)
        ->execute($user, $contract);

    expect($contract->settings()->get(ContractSettingsKey::GithubRepository->value))
        ->toBeNull();
});

it('fails to link when project not owned by user', function () {
    $user = User::factory()->create();
    $contract = Contract::factory()->create();

    (new UnlinkGitRepositoryAction)
        ->execute($user, $contract);
})
    ->expectExceptionMessage('user does not own project');
