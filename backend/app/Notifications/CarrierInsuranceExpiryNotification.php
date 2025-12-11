<?php

namespace App\Notifications;

use App\Models\Carrier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CarrierInsuranceExpiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Carrier $carrier
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $coi = $this->carrier->coi_expires_at ?? null;
        return (new MailMessage)
            ->subject('Carrier insurance expiring: ' . ($this->carrier->name ?? 'Carrier'))
            ->greeting('Heads up!')
            ->line("Carrier: {$this->carrier->name}")
            ->line('MC: ' . ($this->carrier->mc_number ?? 'n/a') . ' | USDOT: ' . ($this->carrier->usd_ot_number ?? 'n/a'))
            ->line('Insurance expires: ' . optional($this->carrier->insurance_expires_at)?->toDateString())
            ->line('COI expires: ' . ($coi ? \Illuminate\Support\Carbon::parse($coi)->toDateString() : 'n/a'))
            ->action('Open Carrier', url("/admin/carriers/{$this->carrier->getKey()}/edit"))
            ->line('Please request updated COI before expiry.');
    }
}
