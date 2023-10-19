<?php

use App\Domain\Contract\Models\Contract;
use App\Domain\Integration\Jobs\Github\HandlePushWebhookJob;
use Spatie\GitHubWebhooks\Models\GitHubWebhookCall;

use function Pest\Laravel\assertDatabaseHas;

it('saves a commit to the database', function () {
    Contract::factory()->create([
        'settings' => [
            'github_repo' => 'PeerMeHQ/bounties-sc',
        ],
    ]);

    $payload = json_decode(file_get_contents(__DIR__.'/payloads/push.json'), true);
    $webhookCall = GitHubWebhookCall::create([
        'url' => 'https://example.com',
        'name' => 'push',
        'payload' => $payload,
    ]);
    $webhookCall->payload = $payload;

    dispatch(new HandlePushWebhookJob($webhookCall));

    assertDatabaseHas('commits', [
        'hash' => '764fe0c54ea3a2e47cfee2ab249156c04343a038',
    ]);
});
