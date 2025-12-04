<?php

namespace App\Notifications;

use App\Models\Load;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoadAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Load $load) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Load {$this->load->load_number} assigned to you")
            ->line("Load {$this->load->load_number} has been assigned.")
            ->line("Client: {$this->load->client?->name}")
            ->line("Carrier: {$this->load->carrier?->name}")
            ->action('View load', url('/admin'))
            ->line('Thank you.');
    }

    public function toArray($notifiable): array
    {
        return [
            'load_id' => $this->load->id,
            'load_number' => $this->load->load_number,
            'message' => 'Load assigned to you',
        ];
    }
}
