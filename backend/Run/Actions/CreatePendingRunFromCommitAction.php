<?php

namespace App\Domain\Run\Actions;

use App\Domain\Commit\Commit;
use App\Domain\Run\Events\RunPendingEvent;
use App\Domain\Run\Models\Run;
use App\Domain\Run\RunStatus;

class CreatePendingRunFromCommitAction
{
    public function execute(Commit $commit)
    {
        $run = $this->saveToDatabase($commit);

        event(new RunPendingEvent($run));

        return $run;
    }

    private function saveToDatabase(Commit $commit): Run
    {
        return $commit->contract->project->runs()->create([
            'status' => RunStatus::Pending,
            'contract_id' => $commit->contract->id,
            'blueprint_id' => null,
            'commit_id' => $commit->id,
        ]);
    }
}
