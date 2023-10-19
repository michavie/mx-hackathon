<?php

use App\Domain\Contract\Models\Contract;
use App\Domain\Project\Models\Project;
use App\Domain\Run\Models\Run;
use App\Domain\Spawn\Models\Blueprint;
use App\Domain\User\Models\User;

use function Pest\Laravel\getJson;

it('returns runs of a contract', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $contract = Contract::factory()->for($project)->create();

    Run::factory()->for($contract)->count(5)->create();

    getJsonAs($user, "runs?contract={$contract->slug}")
        ->assertOk()
        ->assertJsonCount(5, 'data');
});

it('returns runs of a blueprint', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $blueprint = Blueprint::factory()->for($project)->create();

    Run::factory()->for($blueprint)->count(5)->create();

    getJsonAs($user, "runs?blueprint={$blueprint->uuid}")
        ->assertOk()
        ->assertJsonCount(5, 'data');
});

it('fails if unauthenticated', function () {
    getJson('runs')
        ->assertUnauthorized();
});
