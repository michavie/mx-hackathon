<?php

namespace App\Http\Run\Resources;

use App\Domain\Run\RunSettingsKey;
use App\Http\Commit\Resources\CommitResource;
use App\Http\Contract\Resources\ContractResource;
use App\Http\Project\Resources\ProjectResource;
use App\Http\Spawn\Resources\BlueprintResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RunResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getHashid(),
            'status' => $this->status->toName(),
            'project' => new ProjectResource($this->project),
            'contract' => new ContractResource($this->contract),
            'blueprint' => new BlueprintResource($this->blueprint),
            'commit' => new CommitResource($this->commit),
            'duration' => $this->duration,
            'meta' => [
                'tx' => $this->settings()->get(RunSettingsKey::TxHash->value),
                'codeHash' => $this->settings()->get(RunSettingsKey::CodeHash->value),
            ],
            'startedAt' => $this->started_at?->toIso8601String(),
            'createdAt' => $this->created_at->toIso8601String(),
        ];
    }
}
