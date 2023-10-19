<?php

namespace App\Domain\Run\Actions;

use App\Domain\Project\ProjectSettingsKey;
use App\Domain\Run\Events\RunCompletedEvent;
use App\Domain\Run\Jobs\ProcessContractRunArtifactsJob;
use App\Domain\Run\Models\Run;
use App\Domain\Run\Notifications\RunCompletedNotification;
use Illuminate\Support\Facades\Notification;

class CompleteRunAction
{
    public function execute(Run $run, ?string $output, string $runnerVersion): void
    {
        $run->complete($runnerVersion);

        if ($run->contract !== null) {
            dispatch(new ProcessContractRunArtifactsJob($run))
                ->delay(now()->addSeconds(5));
        }

        $run->project->increaseUsage(ProjectSettingsKey::UsageRunsSeconds, $run->duration);

        event(new RunCompletedEvent($run));

        $users = $run->project->getTeamMembers();

        Notification::send($users, new RunCompletedNotification($run));
    }
}
