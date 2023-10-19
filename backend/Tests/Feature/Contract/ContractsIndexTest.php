<?php

use App\Domain\Contract\Models\Contract;
use App\Domain\Project\Models\Project;
use App\Domain\User\Models\User;

use function Pest\Laravel\getJson;

it('returns contracts of a project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    Contract::factory()->for($project)
        ->count(3)
        ->create();

    getJsonAs($user, 'contracts?project='.$project->slug)
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('fails if unauthenticated', function () {
    getJson('contracts')
        ->assertUnauthorized();
});
