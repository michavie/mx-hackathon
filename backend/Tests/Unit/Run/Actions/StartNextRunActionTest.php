<?php

use App\Domain\Run\Actions\StartNextRunAction;
use App\Domain\Run\Events\RunStartedEvent;
use App\Domain\Run\Models\Run;
use App\Domain\Run\Notifications\RunStartedNotification;
use App\Domain\Run\RunInstruction;
use App\Domain\Run\RunStatus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

it('starts the next pending run', function () {
    $pendingRun = Run::factory()->create(['status' => RunStatus::Pending]);

    $action = new StartNextRunAction();
    $instruction = $action->execute();

    $pendingRun->refresh();

    expect($pendingRun->status)->toBe(RunStatus::Active);
    expect($instruction)->toBeInstanceOf(RunInstruction::class);
    expect($instruction->run->id)->toBe($pendingRun->id);
});

it('fires a run started event when a run is started', function () {
    Event::fake();

    $pendingRun = Run::factory()->create(['status' => RunStatus::Pending]);

    $action = new StartNextRunAction();
    $action->execute();

    Event::assertDispatched(RunStartedEvent::class, function ($event) use ($pendingRun) {
        return $event->run->id === $pendingRun->id;
    });
});

it('sends notifications to team members when a run is started', function () {
    Notification::fake();

    $pendingRun = Run::factory()->create(['status' => RunStatus::Pending]);

    $action = new StartNextRunAction();
    $action->execute();

    Notification::assertSentTo(
        $pendingRun->project->getTeamMembers(),
        RunStartedNotification::class,
        function ($notification, $channels) use ($pendingRun) {
            return $notification->run->id === $pendingRun->id;
        }
    );
});

it('returns null if there are no pending runs', function () {
    $action = new StartNextRunAction();
    $instruction = $action->execute();

    expect($instruction)->toBeNull();
});
