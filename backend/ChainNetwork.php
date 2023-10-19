<?php

namespace App\Domain;

use Exception;

enum ChainNetwork: string
{
    case Mainnet = 'mainnet';
    case Testnet = 'testnet';
    case Devnet = 'devnet';

    public function toKey(): string
    {
        return match ($this) {
            self::Mainnet => 'm',
            self::Testnet => 't',
            self::Devnet => 'd',
            default => throw new Exception('unknown network'),
        };
    }
}
