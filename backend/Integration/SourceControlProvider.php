<?php

namespace App\Domain\Integration;

enum SourceControlProvider: int
{
    case Github = 1;

    public function getName(): string
    {
        return match ($this) {
            self::Github => 'github',
        };
    }
}
