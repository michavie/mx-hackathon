<?php

namespace App\Domain\Run;

class RunSettings
{
    public function __construct(
        public ?string $rootDir,
    ) {
    }
}
