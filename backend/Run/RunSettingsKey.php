<?php

namespace App\Domain\Run;

enum RunSettingsKey: string
{
    case RootDir = 'run_root';

    case TxHash = 'tx';

    case CodeHash = 'chash';
}
