<?php

use App\Domain\Contract\Models\Contract;
use App\Domain\Project\Models\Project;
use App\Domain\Run\RunSettings;
use App\Domain\User\Models\User;

use function Pest\Laravel\getJson;

it('returns a runnables settings', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $contract = Contract::factory()->for($project)->create();

    $contract->saveRunSettings(new RunSettings(
        rootDir: 'some-dir',
    ));

    getJsonAs($user, "runs/settings?contract={$contract->slug}")
        ->assertOk('data.rootDir', 'some-dir');
});

it('fails if unauthenticated', function () {
    $contract = Contract::factory()->create();

    getJson("runs/settings?contract={$contract->slug}")
        ->assertUnauthorized();
});
