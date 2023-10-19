<?php

use App\Domain\Contract\Models\Contract;
use App\Domain\Run\Actions\CreatePendingRunAction;
use App\Domain\Run\Events\RunPendingEvent;
use App\Domain\Run\Models\Run;
use App\Domain\Run\RunStatus;
use Illuminate\Support\Facades\Event;

it('creates a pending run for a contract', function () {
    $contract = Contract::factory()->create();

    $run = (new CreatePendingRunAction)->execute($contract);

    expect($run)->toBeInstanceOf(Run::class);
    expect($run->status)->toBe(RunStatus::Pending);
    expect($run->contract_id)->toBe($contract->id);
    expect($run->blueprint_id)->toBeNull();
});

it('fires a run pending event when a run is created', function () {
    Event::fake();
    $contract = Contract::factory()->create();

    (new CreatePendingRunAction)->execute($contract);

    Event::assertDispatched(RunPendingEvent::class, function ($event) use ($contract) {
        return $event->run->contract_id === $contract->id;
    });
});
