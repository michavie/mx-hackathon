<?php

use App\Domain\Contract\Models\Contract;
use App\Domain\Project\Models\Project;
use App\Domain\User\Models\User;

use function Pest\Laravel\getJson;

it('returns a contract', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $contract = Contract::factory()->for($project)->create();

    getJsonAs($user, "contracts/{$contract->slug}")
        ->assertOk();
});

it('fails if unauthenticated', function () {
    getJson('contracts/xyz')
        ->assertUnauthorized();
});
