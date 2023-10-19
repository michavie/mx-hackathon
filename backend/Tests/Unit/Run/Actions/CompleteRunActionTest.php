<?php

use App\Domain\Project\ProjectSettingsKey;
use App\Domain\Run\Actions\CompleteRunAction;
use App\Domain\Run\Events\RunCompletedEvent;
use App\Domain\Run\Models\Run;
use App\Domain\Run\Notifications\RunCompletedNotification;
use App\Domain\Run\RunStatus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

it('completes a run', function () {
    $run = Run::factory()->create([
        'status' => RunStatus::Active,
        'started_at' => now()->subMinute(),
    ]);

    $action = new CompleteRunAction();
    $action->execute($run, null, '1.0.0');

    $run->refresh();

    expect($run->status)->toBe(RunStatus::Success);
    expect($run->runner_version)->toBe('1.0.0');
});

it('fires a run completed event', function () {
    Event::fake();

    $run = Run::factory()->create([
        'status' => RunStatus::Active,
        'started_at' => now()->subMinute(),
    ]);

    $action = new CompleteRunAction();
    $action->execute($run, null, '1.0.0');

    Event::assertDispatched(RunCompletedEvent::class, function ($event) use ($run) {
        return $event->run->id === $run->id;
    });
});

it('sends notifications to team members', function () {
    Notification::fake();

    $run = Run::factory()->create([
        'status' => RunStatus::Active,
        'started_at' => now()->subMinute(),
    ]);

    $action = new CompleteRunAction();
    $action->execute($run, null, '1.0.0');

    Notification::assertSentTo(
        $run->project->getTeamMembers(),
        RunCompletedNotification::class,
        function ($notification, $channels) use ($run) {
            return $notification->run->id === $run->id;
        }
    );
});

it('updates the project settings with the run duration', function () {
    $run = Run::factory()->create([
        'status' => RunStatus::Active,
        'started_at' => now()->subMinutes(2),
    ]);

    $action = new CompleteRunAction();
    $action->execute($run, null, '1.0.0');

    $current = $run->project->settings()->get(ProjectSettingsKey::UsageRunsSeconds->value);
    expect($current)
        ->toBeGreaterThanOrEqual(120)
        ->toBeLessThan(123);
});
