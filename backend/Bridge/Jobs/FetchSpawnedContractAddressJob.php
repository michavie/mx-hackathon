<?php

namespace App\Domain\Bridge\Job;

use App\Domain\Chain;
use App\Domain\ChainNetwork;
use App\Domain\Contract\Models\Contract;
use App\Traits\InteractsWithMultiversxChain;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use InvalidArgumentException;
use Peerme\Mx\Address;
use Peerme\MxProviders\Entities\TransactionDetailed;
use Peerme\MxProviders\Entities\TransactionLogEvent;

class FetchSpawnedContractAddressJob implements ShouldQueue
{
    use InteractsWithMultiversxChain, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Chain $chain,
        public ChainNetwork $network,
        public Contract $contract,
        public string $txHash,
    ) {
    }

    public function handle(): void
    {
        throw_unless($this->txHash, InvalidArgumentException::class, 'tx hash is required');

        try {
            if (! $tx = $this->getSuccessfulTxFromChain($this->chain, $this->network, $this->txHash)) {
                return;
            }

            $event = $this->extractSpawnEvent($tx);

            $contractAddress = $event->identifier === 'SCDeploy'
                ? Address::fromBase64($event->topics[0]) // normal deploy transaction (likely mainnet)
                : Address::fromBase64($event->topics[1]); // deploy via Spawn SC (likely devnet or testnet)

            $this->contract->setAddress(Chain::Multiversx, $this->network, $contractAddress->bech32());
        } catch (Exception $e) {
            $this->handleSilentRetryOrFail($e);
        }
    }

    private function extractSpawnEvent(TransactionDetailed $tx): TransactionLogEvent
    {
        $allowedFunctions = collect(['spawnContract', 'SCDeploy']);

        return $tx->getAllEvents()
            ->filter(fn (TransactionLogEvent $event) => $allowedFunctions->contains($event->identifier))
            ->firstOrFail();
    }
}
