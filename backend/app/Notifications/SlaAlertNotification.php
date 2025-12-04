<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class SlaAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $loadNumber;
    public string $reason;
    public ?string $status;
    public ?int $loadId;

    public function __construct(string $loadNumber, string $reason, ?string $status = null, ?int $loadId = null)
    {
        $this->loadNumber = $loadNumber;
        $this->reason = $reason;
        $this->status = $status;
        $this->loadId = $loadId;
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        // Send mail if an address exists
        if (method_exists($notifiable, 'routeNotificationForMail') || !empty($notifiable->email)) {
            $channels[] = 'mail';
        }
        // Slack if configured
        if (config('services.slack.webhook_url') || config('services.slack.notifications.bot_user_oauth_token')) {
            $channels[] = 'slack';
        }
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = $this->loadId ? route('filament.admin.resources.loads.edit', $this->loadId) : url('/');

        return (new MailMessage)
            ->subject("SLA Alert: {$this->loadNumber}")
            ->line("Load {$this->loadNumber}: {$this->reason}")
            ->line($this->status ? "Current status: {$this->status}" : '')
            ->action('View load', $url);
    }

    public function toSlack(object $notifiable): SlackMessage
    {
        $url = $this->loadId ? route('filament.admin.resources.loads.edit', $this->loadId) : url('/');

        return (new SlackMessage)
            ->warning()
            ->content("SLA Alert • {$this->loadNumber}")
            ->attachment(function ($attachment) use ($url) {
                $attachment->title('View load', $url)
                    ->fields([
                        'Reason' => $this->reason,
                        'Status' => $this->status ?? 'N/A',
                    ]);
            });
    }

    public function toArray(object $notifiable): array
    {
        return [
            'load_number' => $this->loadNumber,
            'reason' => $this->reason,
            'status' => $this->status,
            'load_id' => $this->loadId,
        ];
    }
}
