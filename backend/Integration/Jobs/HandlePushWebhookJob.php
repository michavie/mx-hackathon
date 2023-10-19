<?php

namespace App\Domain\Integration\Jobs\Github;

use App\Domain\Contract\ContractSettingsKey;
use App\Domain\Contract\Models\Contract;
use App\Domain\Integration\SourceControlProvider;
use App\Domain\Run\Actions\CreatePendingRunFromCommitAction;
use App\Domain\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\GitHubWebhooks\Models\GitHubWebhookCall;

class HandlePushWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public GitHubWebhookCall $webhookCall
    ) {
    }

    public function handle(): void
    {
        if (! $repositoryId = $this->webhookCall->payload['repository']['full_name'] ?? null) {
            return;
        }

        Contract::query()
            ->where('settings->'.ContractSettingsKey::GithubRepository->value, $repositoryId)
            ->get()
            ->each(fn (Contract $contract) => $this->saveToDatabase($contract));
    }

    private function saveToDatabase(Contract $contract): void
    {
        $authorEmail = $this->webhookCall->payload['head_commit']['author']['email'];
        $userId = User::where('email', $authorEmail)->first()?->id;

        $commit = $contract->commits()->create([
            'provider' => SourceControlProvider::Github,
            'hash' => $this->webhookCall->payload['head_commit']['id'],
            'branch' => Str::afterLast($this->webhookCall->payload['ref'], '/'),
            'author_name' => $this->webhookCall->payload['head_commit']['author']['name'],
            'author_email' => $authorEmail,
            'message' => $this->webhookCall->payload['head_commit']['message'],
            'committed_at' => Carbon::parse($this->webhookCall->payload['head_commit']['timestamp']),
            'user_id' => $userId,
        ]);

        (new CreatePendingRunFromCommitAction())->execute($commit);
    }
}
