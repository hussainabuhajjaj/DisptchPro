<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class ExpiringCreditsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Collection $credits)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lines = $this->credits->map(function ($c) {
            $exp = $c->expires_at ? $c->expires_at->format('Y-m-d') : 'n/a';
            return "{$c->entity_type} #{$c->entity_id} credit #{$c->id} — remaining $" . number_format($c->remaining, 2) . " (exp {$exp})";
        });

        return (new MailMessage)
            ->subject('Credits expiring soon')
            ->greeting('Heads up!')
            ->line('These credits expire within the next 7 days:')
            ->lines($lines->all());
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Credits expiring soon',
            'lines' => $this->credits->map(function ($c) {
                $exp = $c->expires_at ? $c->expires_at->format('Y-m-d') : 'n/a';
                return "{$c->entity_type} #{$c->entity_id} credit #{$c->id} — $" . number_format($c->remaining, 2) . " (exp {$exp})";
            })->all(),
        ];
    }
}
