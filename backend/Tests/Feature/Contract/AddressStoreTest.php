<?php

use App\Domain\Chain;
use App\Domain\ChainNetwork;
use App\Domain\Contract\Models\Contract;
use App\Domain\Project\Models\Project;
use App\Domain\User\Models\User;

use function Pest\Laravel\postJson;

it('configures a contract', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $contract = Contract::factory()->for($project)->create();

    postJsonAs($user, "contracts/{$contract->slug}/addresses", [
        'network' => ChainNetwork::Mainnet->value,
        'address' => 'erd1qqqqqqqqqqqqqpgqakyy2eaxmv7njv2z6fn4p9makpty3lfpl3tshxnz97',
    ])
        ->assertOk();

    $contract->refresh();

    expect($contract->getAddress(Chain::Multiversx, ChainNetwork::Mainnet))
        ->toBe('erd1qqqqqqqqqqqqqpgqakyy2eaxmv7njv2z6fn4p9makpty3lfpl3tshxnz97');
});

it('fails if unauthenticated', function () {
    postJson('contracts/x/addresses')
        ->assertUnauthorized();
});
