<?php

namespace App\Domain\Bridge\Providers;

use App\Domain\Bridge\Interfaces\IChainBridge;
use App\Domain\Bridge\Job\FetchSpawnedBlueprintAddressJob;
use App\Domain\Bridge\Job\FetchSpawnedContractAddressJob;
use App\Domain\Chain;
use App\Domain\ChainNetwork;
use App\Domain\Contract\Models\Contract;
use App\Domain\Run\Models\Run;
use App\Domain\Run\RunSettingsKey;
use App\Domain\Spawn\BlueprintSettingsKey;
use App\Domain\Spawn\MllViewer;
use App\Domain\Spawn\Models\Blueprint;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use kornrunner\Keccak;

class MultiversxBlockchainBridge implements IChainBridge
{
    private $apiBaseUrl = '<url-to-hosted-bridge-service>';

    public function deployContract(ChainNetwork $network, Contract $contract, Run $run): void
    {
        $artifactUrl = $run->getArtifact()->getTemporaryUrl(now()->addHours(12));
        $isFirstDeployment = $contract->getAddress(Chain::Multiversx, $network) === null;

        $txHash = $this->getConfiguredHttpClient()
            ->post($this->apiBaseUrl.'/contracts/deploy', [
                'network' => $network->value,
                'contract' => [
                    'address' => $contract->getAddress(Chain::Multiversx, $network),
                    'initArgs' => $contract->getInitArgs(Chain::Multiversx, $network),
                ],
                'artifact' => [
                    'url' => $artifactUrl,
                ],
            ])
            ->json()['data']['tx'] ?? null;

        $run->settings()->set(RunSettingsKey::TxHash->value, $txHash);

        if ($isFirstDeployment) {
            dispatch(new FetchSpawnedContractAddressJob(Chain::Multiversx, $network, $contract, $txHash))
                ->delay(config('multiversx.block_time') * 2);
        }
    }

    public function executeTx(ChainNetwork $network, array $txObject): ?string
    {
        $txHash = $this->getConfiguredHttpClient()
            ->post($this->apiBaseUrl.'/contracts/call', [
                'network' => $network->value,
                'tx' => $txObject,
            ])
            ->json()['data']['tx'] ?? null;

        return $txHash;
    }

    private function getConfiguredHttpClient(): PendingRequest
    {
        return Http::withToken(config('services.bridge_mx.api_key'))
            ->asJson()
            ->throw();
    }
}
