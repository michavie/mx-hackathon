<?php

use App\Domain\Run\Actions\UpdateRunAction;
use App\Domain\Run\Events\RunUpdatedEvent;
use App\Domain\Run\Models\Run;
use Illuminate\Support\Facades\Event;

it('fires the RunUpdatedEvent if there is a non-null output', function () {
    Event::fake();

    $run = Run::factory()->create();
    $output = 'Sample Output';

    $action = new UpdateRunAction();
    $action->execute($run, $output);

    Event::assertDispatched(RunUpdatedEvent::class, function ($event) use ($run, $output) {
        return $event->run->id === $run->id && $event->output === $output;
    });
});

it('does not fire the RunUpdatedEvent if the output is null', function () {
    Event::fake();

    $run = Run::factory()->create();

    $action = new UpdateRunAction();
    $action->execute($run, null);

    Event::assertNotDispatched(RunUpdatedEvent::class);
});
