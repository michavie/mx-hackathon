<?php

use App\Domain\Contract\Actions\CreateContractAction;
use App\Domain\Contract\Models\Contract;
use App\Domain\Project\Models\Project;
use App\Domain\User\Models\User;

use function Pest\Laravel\assertDatabaseHas;

it('stores a contract in the database', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $actual = (new CreateContractAction)->execute($user, $project, 'My Contract');

    expect($actual)->toBeInstanceOf(Contract::class);

    assertDatabaseHas('contracts', [
        'id' => $actual->id,
        'name' => 'My Contract',
    ]);
});

it('fails if user does not own contract', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();

    (new CreateContractAction)->execute($user, $project, 'My Contract');
})
    ->expectExceptionMessage('user does not own project');
