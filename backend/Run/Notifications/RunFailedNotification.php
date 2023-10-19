<?php

namespace App\Domain\Run\Notifications;

use App\Domain\Notification\Dtos\NotificationData;
use App\Domain\Notification\NotificationBase;
use App\Domain\NotificationType;
use App\Domain\Run\Models\Run;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class RunFailedNotification extends NotificationBase implements ShouldQueue
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
            return "Build failed for {$this->run->contract->name}";
        }

        return 'Run completed';
    }

    private function getDescription(): string
    {
        if ($this->run->contract) {
            return 'Learn more about the error in the Dashboard.';
        }

        return "Duration: {$this->run->duration} seconds.";
    }
}
