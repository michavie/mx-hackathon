<?php

use App\Domain\Chain;
use App\Domain\ChainNetwork;
use App\Domain\Contract\Models\Contract;
use App\Domain\Project\Models\Project;
use App\Domain\User\Models\User;

use function Pest\Laravel\postJson;

it('sets initial arguments for a contract', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $contract = Contract::factory()->for($project)->create();

    postJsonAs($user, "contracts/{$contract->slug}/init-args", [
        'network' => 'testnet',
        'args' => [
            'foo',
            'bar',
        ],
    ])
        ->assertOk();

    $contract->refresh();

    expect($contract->getInitArgs(Chain::Multiversx, ChainNetwork::Testnet))
        ->toBe(['foo', 'bar']);
});

it('fails if unauthenticated', function () {
    postJson('contracts/x/init-args')
        ->assertUnauthorized();
});
