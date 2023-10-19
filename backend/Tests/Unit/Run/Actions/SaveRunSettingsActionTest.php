<?php

namespace Tests\Unit\Run\Actions;

use App\Domain\Contract\Models\Contract;
use App\Domain\Run\Actions\SaveRunSettingsAction;
use App\Domain\Run\RunSettings;
use App\Domain\Run\RunSettingsKey;
use App\Domain\User\Models\User;
use InvalidArgumentException;

beforeEach(function () {
    $this->action = new SaveRunSettingsAction();
    $this->user = User::factory()->create();
    $this->settings = new RunSettings(
        rootDir: 'sample/path',
    );
});

it('saves settings for a valid contract runnable', function () {
    $contract = Contract::factory()->create();

    $this->action->execute($this->user, $contract, $this->settings);

    expect($contract->settings()->get(RunSettingsKey::RootDir->value))
        ->toBe('sample/path');
});

it('throws exception for an invalid runnable type', function () {
    $invalidRunnable = User::factory()->create();

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('invalid runnable type');

    $this->action->execute($this->user, $invalidRunnable, $this->settings);
});
