<?php

namespace App\Http\Run\Converters;

use App\Domain\Run\RunInstruction;
use Illuminate\Support\Collection;

class RunInstructionResponseConverter
{
    public static function single(RunInstruction $instruction): array
    {
        return [
            'runId' => $instruction->run->getHashid(),
            'runType' => $instruction->getRunType(),
            'githubRepoName' => $instruction->getGithubRepoName(),
            'githubOAuthToken' => $instruction->getGithubOAuthToken(),
            'steps' => $instruction->getSteps(),
            'settings' => $instruction->run->getSettings(),
            'runnerVersionLatest' => $instruction->getRunnerVersionLatest(),
        ];
    }

    public static function multiple(Collection $instructions): array
    {
        return $instructions
            ->map(fn (RunInstruction $instruction) => self::single($instruction))
            ->toArray();
    }
}
