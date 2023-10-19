<?php

use App\Domain\Chain;
use App\Domain\ChainNetwork;
use App\Domain\Contract\Actions\LinkGitRepositoryAction;
use App\Domain\Contract\Models\Contract;
use App\Domain\Integration\SourceControlProvider;
use App\Domain\Project\Models\Project;
use App\Domain\Run\RunStatus;
use App\Domain\User\Models\User;

use function Pest\Laravel\assertDatabaseHas;

it('links a contract to a git repository', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $contract = Contract::factory()->for($project)->create();

    (new LinkGitRepositoryAction)
        ->execute($user, $contract, SourceControlProvider::Github, 'org/some-repo');

    assertDatabaseHas('contracts', [
        'id' => $contract->id,
        'settings->github_repo' => 'org/some-repo',
    ]);
});

it('unsets existing devnet addresses', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $contract = Contract::factory()->for($project)->create();

    $contract->setAddress(Chain::Multiversx, ChainNetwork::Devnet, 'erd1qqqqq');

    (new LinkGitRepositoryAction)
        ->execute($user, $contract, SourceControlProvider::Github, 'org/some-repo');

    $contract->refresh();

    expect($contract->getAddress(Chain::Multiversx, ChainNetwork::Devnet))
        ->toBeNull();
});

it('starts a run', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $contract = Contract::factory()->for($project)->create();

    (new LinkGitRepositoryAction)
        ->execute($user, $contract, SourceControlProvider::Github, 'org/some-repo');

    $contract->refresh();

    expect($contract->project->runs()->count())
        ->toBe(1);

    expect($contract->project->runs()->first()->status)
        ->toBe(RunStatus::Pending);
});

it('fails to link when project not owned by user', function () {
    $user = User::factory()->create();
    $contract = Contract::factory()->create();

    (new LinkGitRepositoryAction)
        ->execute($user, $contract, SourceControlProvider::Github, 'org/some-repo');
})
    ->expectExceptionMessage('user does not own project');
