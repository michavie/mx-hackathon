<?php

namespace App\Domain\Run\Models;

use App\Domain\Commit\Commit;
use App\Domain\Contract\Models\Contract;
use App\Domain\Project\Models\Project;
use App\Domain\Run\RunSettings;
use App\Domain\Run\RunStatus;
use App\Domain\Spawn\Models\Blueprint;
use App\Traits\Hashidable;
use Database\Factories\RunFactory;
use Exception;
use Glorand\Model\Settings\Traits\HasSettingsField;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Run extends Model implements HasMedia
{
    use HasFactory, Hashidable, HasSettingsField, InteractsWithMedia;

    const MediaLibraryCollectionArtifacts = 'artifacts';

    protected $guarded = [];

    protected $casts = [
        'status' => RunStatus::class,
        'started_at' => 'datetime',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MediaLibraryCollectionArtifacts)
            ->acceptsMimeTypes(['application/zip'])
            ->singleFile();
    }

    public function getArtifact(): ?Media
    {
        return $this->getFirstMedia(self::MediaLibraryCollectionArtifacts);
    }

    public function getSettings(): ?RunSettings
    {
        if ($this->contract_id) {
            return $this->contract->getRunSettings();
        }

        return null;
    }

    public function start(): void
    {
        throw_unless($this->status === RunStatus::Pending, Exception::class, 'run must be pending');

        $this->update([
            'status' => RunStatus::Active,
            'started_at' => now(),
        ]);
    }

    public function complete(string $runnerVersion): void
    {
        throw_unless($this->status === RunStatus::Active, Exception::class, 'run must be active');

        $this->update([
            'status' => RunStatus::Success,
            'duration' => $this->started_at->diffInSeconds(now()),
            'runner_version' => $runnerVersion,
        ]);
    }

    public function cancel(): void
    {
        throw_unless($this->status === RunStatus::Active, Exception::class, 'run must be running');

        $this->update([
            'status' => RunStatus::Canceled,
            'duration' => $this->started_at->diffInSeconds(now()),
        ]);
    }

    public function fail(string $runnerVersion): void
    {
        throw_unless($this->status === RunStatus::Active, Exception::class, 'run must be running');

        $this->update([
            'status' => RunStatus::Failed,
            'duration' => $this->started_at->diffInSeconds(now()),
            'runner_version' => $runnerVersion,
        ]);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function commit(): BelongsTo
    {
        return $this->belongsTo(Commit::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    protected static function newFactory()
    {
        return RunFactory::new();
    }
}
