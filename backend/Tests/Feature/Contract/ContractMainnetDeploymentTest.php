<?php

use App\Domain\Bridge\Job\FetchSpawnedContractAddressJob;
use App\Domain\Contract\Models\Contract;
use App\Domain\Project\Models\Project;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\postJson;

it('dispatches an address fetching job', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $contract = Contract::factory()->for($project)->create();

    Queue::fake();

    postJsonAs($user, "contracts/{$contract->slug}/mainnet-deployment", [
        'tx' => '7fd9303fa6019162ded85be6d0703234a52f2344ce003f23afdef90c58e8e4ec',
    ])
        ->assertOk();

    Queue::assertPushed(FetchSpawnedContractAddressJob::class);
});

it('fails if unauthenticated', function () {
    postJson('contracts/x/mainnet-deployment')
        ->assertUnauthorized();
});
