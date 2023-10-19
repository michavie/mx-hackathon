<?php

use App\Domain\Run\Models\Run;
use App\Domain\Run\RunStatus;
use Illuminate\Support\Facades\Config;

use function Pest\Laravel\postJson;

it('returns the next pending run', function () {
    Run::factory()->create([
        'status' => RunStatus::Pending,
    ]);

    Config::set('app.runs.runner_auth_tokens', ['valid', 'other']);

    postJson('runs/runners/next', [], [
        'Authorization' => 'valid',
    ])
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('fails when invalid auth token', function () {
    postJson('runs/runners/next', [], [
        'Authorization' => 'invalid',
    ])
        ->assertUnauthorized();
});
