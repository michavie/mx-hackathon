<?php

namespace App\Domain\Run;

use InvalidArgumentException;

enum RunStatus: int
{
    case Pending = 0;
    case Active = 1;
    case Success = 2;
    case Failed = 3;
    case Canceled = 4;

    public static function fromName(string $name): self
    {
        return match ($name) {
            'pending' => self::Pending,
            'active' => self::Active,
            'success' => self::Success,
            'failed' => self::Failed,
            'canceled' => self::Canceled,
            default => throw new InvalidArgumentException("invalid run status name: {$name}"),
        };
    }

    public function toName(): string
    {
        return match ($this) {
            self::Pending => 'pending',
            self::Active => 'active',
            self::Success => 'success',
            self::Failed => 'failed',
            self::Canceled => 'canceled',
        };
    }
}
