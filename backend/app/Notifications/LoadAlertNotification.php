<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoadAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $message,
        public array $data = []
    ) {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Load Alert')
            ->line($this->message)
            ->line($this->data['load_number'] ?? '')
            ->action('View load', $this->data['url'] ?? url('/admin/loads'));
    }

    public function toArray($notifiable): array
    {
        return $this->data + ['message' => $this->message];
    }
}
