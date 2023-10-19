<?php

use App\Domain\Commit\Commit;
use App\Domain\Run\Actions\CreatePendingRunFromCommitAction;
use App\Domain\Run\Events\RunPendingEvent;
use App\Domain\Run\Models\Run;
use App\Domain\Run\RunStatus;
use Illuminate\Support\Facades\Event;

it('creates a pending run from a commit', function () {
    $commit = Commit::factory()->create();

    $action = new CreatePendingRunFromCommitAction();
    $run = $action->execute($commit);

    expect($run)->toBeInstanceOf(Run::class);
    expect($run->status)->toBe(RunStatus::Pending);
    expect($run->contract_id)->toBe($commit->contract->id);
    expect($run->blueprint_id)->toBeNull();
});

it('fires a run pending event when a run is created', function () {
    Event::fake();

    $commit = Commit::factory()->create();

    $action = new CreatePendingRunFromCommitAction();
    $action->execute($commit);

    Event::assertDispatched(RunPendingEvent::class, function ($event) use ($commit) {
        return $event->run->contract_id === $commit->contract->id;
    });
});
