<?php

use App\Domain\Run\Models\Run;
use App\Domain\Run\RunStatus;
use Illuminate\Support\Facades\Config;

use function Pest\Laravel\postJson;

it('returns ok for an update', function () {
    $run = Run::factory()->create([
        'status' => RunStatus::Active,
    ]);

    Config::set('app.runs.runner_auth_tokens', ['valid', 'other']);

    postJson('runs/runners/update', [
        'version' => '0.1.0',
        'runId' => $run->getHashid(),
        'status' => RunStatus::Active->toName(),
    ], [
        'Authorization' => 'valid',
    ])
        ->assertOk();
});

it('fails when invalid auth token', function () {
    postJson('runs/runners/update', [], [
        'Authorization' => 'invalid',
    ])
        ->assertUnauthorized();
});
