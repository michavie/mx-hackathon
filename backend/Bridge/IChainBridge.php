<?php

namespace App\Domain\Bridge\Interfaces;

use App\Domain\ChainNetwork;
use App\Domain\Contract\Models\Contract;
use App\Domain\Run\Models\Run;
use App\Domain\Spawn\Models\Blueprint;

interface IChainBridge
{
    public function deployContract(ChainNetwork $network, Contract $contract, Run $run): void;

    public function executeTx(ChainNetwork $network, array $txObject): ?string;
}
