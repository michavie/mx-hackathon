<?php

namespace App\Http\Run\Controllers;

use App\Domain\Run\Actions\CompleteRunAction;
use App\Domain\Run\Actions\FailRunAction;
use App\Domain\Run\Actions\StartNextRunAction;
use App\Domain\Run\Actions\UpdateRunAction;
use App\Domain\Run\Models\Run;
use App\Domain\Run\RunStatus;
use App\Http\Controller;
use App\Http\Run\Converters\RunInstructionResponseConverter;
use App\Http\Run\Middleware\RunnerAuthMiddleware;
use App\Http\Run\Requests\RunNextRequest;
use App\Http\Run\Requests\RunUpdateRequest;
use InvalidArgumentException;

class RunnersController extends Controller
{
    public function __construct()
    {
        $this->middleware(RunnerAuthMiddleware::class);
    }

    public function next(RunNextRequest $request, StartNextRunAction $startNextRunAction)
    {
        $next = $startNextRunAction->execute();

        if ($next === null) {
            return $this->ok();
        }

        return $this->ok(RunInstructionResponseConverter::multiple(
            collect([$next])
        ));
    }

    public function update(RunUpdateRequest $request, UpdateRunAction $updateRunAction, CompleteRunAction $completeRunAction, FailRunAction $failRunAction)
    {
        $status = RunStatus::fromName($request->input('status'));
        $run = Run::getModelFromHashidOrFail($request->input('runId'));
        $output = $request->get('output');
        $version = $request->get('version');

        if ($request->has('artifacts')) {
            $run
                ->addMediaFromRequest('artifacts')
                ->toMediaCollection(Run::MediaLibraryCollectionArtifacts);
        }

        match ($status) {
            RunStatus::Active => $updateRunAction->execute($run, $output),
            RunStatus::Success => $completeRunAction->execute($run, $output, $version),
            RunStatus::Failed => $failRunAction->execute($run, $output, $version),
            default => throw new InvalidArgumentException("invalid run status: {$status->value}"),
        };

        return $this->ok();
    }
}
