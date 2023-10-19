<?php

namespace App\Domain\Run\Actions;

use App\Domain\Contract\Models\Contract;
use App\Domain\Run\Events\RunPendingEvent;
use App\Domain\Run\Models\Run;
use App\Domain\Run\RunStatus;
use App\Domain\Spawn\Models\Blueprint;
use Illuminate\Database\Eloquent\Model;

class CreatePendingRunAction
{
    public function execute(Model $runnable)
    {
        $run = $this->saveToDatabase($runnable);

        event(new RunPendingEvent($run));

        return $run;
    }

    private function saveToDatabase(Model $runnable): Run
    {
        return $runnable->project->runs()->create([
            'status' => RunStatus::Pending,
            'contract_id' => $runnable instanceof Contract ? $runnable->id : null,
            'blueprint_id' => $runnable instanceof Blueprint ? $runnable->id : null,
            'commit_id' => null,
        ]);
    }
}
