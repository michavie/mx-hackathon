<?php

use App\Domain\Project\ProjectSettingsKey;
use App\Domain\Run\Actions\FailRunAction;
use App\Domain\Run\Events\RunFailedEvent;
use App\Domain\Run\Models\Run;
use App\Domain\Run\Notifications\RunFailedNotification;
use App\Domain\Run\RunStatus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

it('fails a run', function () {
    $run = Run::factory()->create([
        'status' => RunStatus::Active,
        'started_at' => now()->subMinute(),
    ]);

    $action = new FailRunAction();
    $action->execute($run, null, '1.0.0');

    $run->refresh();
    expect($run->status)->toBe(RunStatus::Failed);
    expect($run->runner_version)->toBe('1.0.0');
});

it('fires a run failed event', function () {
    Event::fake();

    $run = Run::factory()->create([
        'status' => RunStatus::Active,
        'started_at' => now()->subMinute(),
    ]);

    $action = new FailRunAction();
    $action->execute($run, null, '1.0.0');

    Event::assertDispatched(RunFailedEvent::class, function ($event) use ($run) {
        return $event->run->id === $run->id;
    });
});

it('sends notifications to team members', function () {
    Notification::fake();

    $run = Run::factory()->create([
        'status' => RunStatus::Active,
        'started_at' => now()->subMinute(),
    ]);

    $action = new FailRunAction();
    $action->execute($run, null, '1.0.0');

    Notification::assertSentTo(
        $run->project->getTeamMembers(),
        RunFailedNotification::class,
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

    $action = new FailRunAction();
    $action->execute($run, null, '1.0.0');

    $current = $run->project->settings()->get(ProjectSettingsKey::UsageRunsSeconds->value);
    expect($current)
        ->toBeGreaterThanOrEqual(120)
        ->toBeLessThan(123);
});
