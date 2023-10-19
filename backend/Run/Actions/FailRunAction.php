<?php

namespace App\Domain\Run\Actions;

use App\Domain\Project\ProjectSettingsKey;
use App\Domain\Run\Events\RunFailedEvent;
use App\Domain\Run\Models\Run;
use App\Domain\Run\Notifications\RunFailedNotification;
use Illuminate\Support\Facades\Notification;

class FailRunAction
{
    public function execute(Run $run, ?string $output, string $runnerVersion)
    {
        $run->fail($runnerVersion);

        event(new RunFailedEvent($run));

        $run->project->increaseUsage(ProjectSettingsKey::UsageRunsSeconds, $run->duration);

        $users = $run->project->getTeamMembers();

        Notification::send($users, new RunFailedNotification($run));

    }
}
