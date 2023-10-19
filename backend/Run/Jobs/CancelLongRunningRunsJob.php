<?php

namespace App\Domain\Run\Jobs;

use App\Domain\Run\Models\Run;
use App\Domain\Run\RunStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CancelLongRunningRunsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $threshold = now()->subHours(2);

        $canceledCount = Run::query()
            ->where('started_at', '<', $threshold)
            ->where('status', RunStatus::Active)
            ->update(['status' => RunStatus::Canceled]);

        if ($canceledCount > 0) {
            Log::info("Canceled {$canceledCount} runs that were running for too long");
        }
    }
}
