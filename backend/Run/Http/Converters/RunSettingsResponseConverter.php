<?php

namespace App\Http\Run\Converters;

use App\Domain\Run\RunSettings;

class RunSettingsResponseConverter
{
    public static function single(RunSettings $settings): array
    {
        return [
            'rootDir' => $settings->rootDir,
        ];
    }
}
