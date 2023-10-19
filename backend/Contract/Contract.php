<?php

namespace App\Domain\Contract\Models;

use App\Domain\Chain;
use App\Domain\ChainNetwork;
use App\Domain\Commit\Commit;
use App\Domain\Contract\ContractSettingsKey;
use App\Domain\Project\Models\Project;
use App\Domain\Run\Models\Run;
use App\Domain\Run\RunSettings;
use App\Domain\Run\RunSettingsKey;
use Database\Factories\ContractFactory;
use Glorand\Model\Settings\Traits\HasSettingsField;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Contract extends Model implements HasMedia
{
    use HasFactory, HasSettingsField, HasSlug, InteractsWithMedia;

    const MediaLibraryCollectionAbi = 'abi';

    public $fillable = [
        'name', 'addresses',
    ];

    public $casts = [
        'addresses' => 'array',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MediaLibraryCollectionAbi)
            ->acceptsMimeTypes(['application/json'])
            ->singleFile();
    }

    public function setAddress(Chain $chain, ChainNetwork $network, string $address): void
    {
        $addresses = $this->addresses ?? [];
        $addresses[$chain->toKey()][$network->toKey()] = $address;
        $this->addresses = $addresses;
        $this->save();
    }

    public function unsetAddress(Chain $chain, ChainNetwork $network): void
    {
        $addresses = $this->addresses;
        unset($addresses[$chain->toKey()][$network->toKey()]);
        $this->addresses = $addresses;
        $this->save();
    }

    public function getAddress(Chain $chain, ChainNetwork $network): ?string
    {
        return $this->addresses[$chain->toKey()][$network->toKey()] ?? null;
    }

    public function setInitArgs(Chain $chain, ChainNetwork $network, array $args): void
    {
        $this->settings()->set(ContractSettingsKey::InitArgs->toChainNestedKey($chain, $network), $args);
    }

    public function getInitArgs(Chain $chain, ChainNetwork $network): array
    {
        return $this->settings()->get(ContractSettingsKey::InitArgs->toChainNestedKey($chain, $network)) ?? [];
    }

    public function getAbiUrl(): ?string
    {
        return $this->settings()->get(ContractSettingsKey::AbiExternalUrl->value)
            ?? $this->getFirstMediaUrl(Contract::MediaLibraryCollectionAbi);
    }

    public function getRunSettings(): RunSettings
    {
        return new RunSettings(
            rootDir: $this->settings()->get(RunSettingsKey::RootDir->value),
        );
    }

    public function saveRunSettings(RunSettings $settings): void
    {
        $this->settings()->setMultiple([
            RunSettingsKey::RootDir->value => $settings->rootDir,
        ]);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function commits(): HasMany
    {
        return $this->hasMany(Commit::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(Run::class);
    }

    protected static function newFactory()
    {
        return ContractFactory::new();
    }
}
