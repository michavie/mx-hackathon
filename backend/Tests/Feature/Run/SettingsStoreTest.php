<?php

use App\Domain\Contract\Models\Contract;
use App\Domain\Project\Models\Project;
use App\Domain\Run\RunSettingsKey;
use App\Domain\User\Models\User;

use function Pest\Laravel\postJson;

it('stores a runnables settings', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $contract = Contract::factory()->for($project)->create();

    postJsonAs($user, "runs/settings?contract={$contract->slug}", [
        'rootDir' => 'some-dir',
    ])
        ->assertOk();

    expect($contract->refresh()->settings()->get(RunSettingsKey::RootDir->value))
        ->toBe('some-dir');
});

it('fails if unauthenticated', function () {
    $contract = Contract::factory()->create();

    postJson("runs/settings?contract={$contract->slug}")
        ->assertUnauthorized();
});
