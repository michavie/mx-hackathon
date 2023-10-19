<?php

namespace App\Domain\Run\Actions;

use App\Domain\Contract\Models\Contract;
use App\Domain\Run\RunSettings;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class SaveRunSettingsAction
{
    public function execute(User $user, Model $runnable, RunSettings $settings): void
    {
        // TODO: validate user has permission to update settings

        if ($runnable instanceof Contract) {
            $runnable->saveRunSettings($settings);
        } else {
            throw new InvalidArgumentException('invalid runnable type');
        }
    }
}
