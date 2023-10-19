<?php

use App\Domain\Run\Jobs\CancelLongRunningRunsJob;
use App\Domain\Run\Models\Run;
use App\Domain\Run\RunStatus;

it('cancels runs that have been running for too long', function () {
    $oldRunStillRunning = Run::factory()->create([
        'started_at' => now()->subHours(3),
        'status' => RunStatus::Active,
    ]);

    CancelLongRunningRunsJob::dispatchSync();

    expect($oldRunStillRunning->refresh()->status->value)
        ->toBe(RunStatus::Canceled->value);
});

it('does not cancel recent runs', function () {
    $recentRunStillRunning = Run::factory()->create([
        'started_at' => now()->subMinutes(30),
        'status' => RunStatus::Active,
    ]);

    CancelLongRunningRunsJob::dispatchSync();

    expect($recentRunStillRunning->refresh()->status->value)
        ->toBe(RunStatus::Active->value);
});
