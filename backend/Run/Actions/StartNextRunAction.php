<?php

namespace App\Domain\Run\Actions;

use App\Domain\Run\Events\RunStartedEvent;
use App\Domain\Run\Models\Run;
use App\Domain\Run\Notifications\RunStartedNotification;
use App\Domain\Run\RunInstruction;
use App\Domain\Run\RunStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class StartNextRunAction
{
    public function execute(): ?RunInstruction
    {
        $nextRun = DB::transaction(function () {
            $next = Run::where('status', RunStatus::Pending)
                ->lockForUpdate()
                ->first();

            $next?->start();

            return $next;
        });

        if ($nextRun === null) {
            return null;
        }

        event(new RunStartedEvent($nextRun));

        $this->sendNotifications($nextRun);

        return $this->makeInstruction($nextRun);
    }

    private function makeInstruction(Run $run): RunInstruction
    {
        return new RunInstruction(
            run: $run,
            userSteps: $this->getSteps(),
        );
    }

    private function getSteps(): array
    {
        return []; // TODO: let users configure additonal steps on frontend
    }

    private function sendNotifications(Run $run): void
    {
        $users = $run->project->getTeamMembers();

        Notification::send($users, new RunStartedNotification($run));
    }
}
