<?php

namespace App\Domain\Contract\Actions;

use App\Domain\Contract\Models\Contract;
use App\Domain\Log\AdminLog;
use App\Domain\Log\AdminLogChannel;
use App\Domain\Project\Models\Project;
use App\Domain\User\Models\User;
use InvalidArgumentException;

class CreateContractAction
{
    public function execute(User $user, Project $project, string $name): Contract
    {
        throw_unless($user, InvalidArgumentException::class, 'user is required');
        throw_unless($project, InvalidArgumentException::class, 'project is required');
        throw_unless($name, InvalidArgumentException::class, 'name is required');
        throw_unless($user->owns($project), InvalidArgumentException::class, 'user does not own project');

        $contract = $this->saveToDatabase($project, $name);

        return $contract;
    }

    private function saveToDatabase(Project $project, string $name): Contract
    {
        return $project->contracts()->create([
            'name' => strip_tags(trim($name)),
            'addresses' => [],
        ]);
    }
}
