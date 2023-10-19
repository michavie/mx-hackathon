<?php

namespace App\Domain\Run\Notifications;

use App\Domain\Notification\Dtos\NotificationData;
use App\Domain\Notification\NotificationBase;
use App\Domain\NotificationType;
use App\Domain\Run\Models\Run;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class RunStartedNotification extends NotificationBase implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Run $run,
    ) {
    }

    public static function notificationType(): string
    {
        return NotificationType::Runs;
    }

    public function via($notifiable)
    {
        return $notifiable->getNotificationChannelsFor(static::notificationType());
    }

    public function toNotificationData($notifiable): NotificationData
    {
        return new NotificationData(
            title: $this->getTitle(),
            message: $this->getDescription(),
        );
    }

    public function getWebPageUrl($notifiable): ?string
    {
        return '#'; // TODO
    }

    private function getTitle(): string
    {
        if ($this->run->contract) {
            return "Build started for {$this->run->contract->name}";
        }

        return 'Run started';
    }

    private function getDescription(): string
    {
        if ($this->run->contract) {
            return 'It will be deployable in a few minutes.';
        }

        return 'It will complete in a few minutes.';
    }
}
