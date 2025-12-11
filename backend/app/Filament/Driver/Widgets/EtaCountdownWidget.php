<?php

namespace App\Filament\Driver\Widgets;

use App\Models\Load;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class EtaCountdownWidget extends Widget
{
    protected string $view = 'filament.driver.widgets.eta-countdown-widget';

    public ?array $eta = null;

    public function mount(): void
    {
        $driver = Auth::guard('driver')->user();
        if (!$driver) {
            return;
        }

        $load = Load::query()
            ->where('driver_id', $driver->id)
            ->whereNotIn('status', ['delivered', 'completed', 'cancelled'])
            ->orderByDesc('last_location_at')
            ->first();

        if (!$load || !$load->last_eta_minutes) {
            return;
        }

        $this->eta = [
            'load_number' => $load->load_number,
            'eta_minutes' => $load->last_eta_minutes,
            'last_ping' => optional($load->last_location_at)?->diffForHumans(),
        ];
    }
}
