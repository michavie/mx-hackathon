<?php

namespace App\Domain\Run\Actions;

use App\Domain\Run\Events\RunUpdatedEvent;
use App\Domain\Run\Models\Run;

class UpdateRunAction
{
    public function execute(Run $run, ?string $output)
    {
        if ($output !== null) {
            event(new RunUpdatedEvent($run, $output));
        }
    }
}
