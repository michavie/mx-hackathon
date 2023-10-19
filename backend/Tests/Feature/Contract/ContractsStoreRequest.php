<?php

use App\Domain\Project\Models\Project;
use App\Domain\User\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

it('fails if unauthenticated', function () {
    postJson('contracts')
        ->assertUnauthorized();
});

it('stores a contract', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    postJsonAs($user, "contracts?project={$project->slug}", [
        'name' => 'My Contract',
    ])
        ->assertOk();

    assertDatabaseHas('contracts', [
        'name' => 'My Contract',
    ]);
});
