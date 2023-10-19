<?php

use App\Domain\Contract\Models\Contract;
use App\Domain\Project\Models\Project;
use App\Domain\Run\RunStatus;
use App\Domain\User\Models\User;

use function Pest\Laravel\postJson;

it('stores a pending run for a contract', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $contract = Contract::factory()->for($project)->create();

    postJsonAs($user, "runs?contract={$contract->slug}")
        ->assertOk();

    expect($contract->runs()->count())
        ->toBe(1);

    expect($contract->runs()->first()->status->value)
        ->toBe(RunStatus::Pending->value);
});

it('fails if unauthenticated', function () {
    postJson('runs')
        ->assertUnauthorized();
});
