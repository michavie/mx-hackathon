<?php

namespace App\Domain\Contract;

use App\Domain\Chain;
use App\Domain\ChainNetwork;

enum ContractSettingsKey: string
{
    case GithubRepository = 'github_repo';

    case RepositoryLinkedAt = 'repo_linked_at';

    case AbiExternalUrl = 'abi_url';

    case InitArgs = 'init';

    public function toChainNestedKey(Chain $chain, ChainNetwork $network): string
    {
        return $this->value.'.'.$chain->toKey().'.'.$network->toKey();
    }
}
