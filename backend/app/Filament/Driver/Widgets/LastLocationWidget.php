<?php

namespace App\Filament\Driver\Widgets;

use App\Models\Load;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class LastLocationWidget extends Widget
{
    protected string $view = 'filament.driver.widgets.last-location-widget';

    public ?array $location = null;

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

        if (!$load || !$load->last_lat || !$load->last_lng) {
            return;
        }

        $this->location = [
            'load_number' => $load->load_number,
            'lat' => (float) $load->last_lat,
            'lng' => (float) $load->last_lng,
            'last_ping' => optional($load->last_location_at)?->toIso8601String(),
        ];
    }
}
