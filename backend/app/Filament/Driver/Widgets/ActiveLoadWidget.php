<?php

namespace App\Filament\Driver\Widgets;

use App\Models\Load;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class ActiveLoadWidget extends Widget
{
    protected string $view = 'filament.driver.widgets.active-load-widget';

    public array $loads = [];

    public function mount(): void
    {
        $driver = Auth::guard('driver')->user();
        if (!$driver) {
            $this->loads = [];
            return;
        }

        $this->loads = Load::query()
            ->with(['stops' => fn ($q) => $q->orderBy('sequence')])
            ->where('driver_id', $driver->id)
            ->whereNotIn('status', ['delivered', 'completed', 'cancelled'])
            ->orderByDesc('last_location_at')
            ->limit(3)
            ->get()
            ->map(function (Load $load) {
                $nextStop = $load->stops->first();
                return [
                    'id' => $load->id,
                    'load_number' => $load->load_number,
                    'status' => $load->status,
                    'next_stop' => $nextStop ? [
                        'type' => $nextStop->type,
                        'city' => $nextStop->city,
                        'state' => $nextStop->state,
                        'date_from' => optional($nextStop->date_from)?->toIso8601String(),
                    ] : null,
                    'eta_minutes' => $load->last_eta_minutes,
                    'last_ping_at' => optional($load->last_location_at)?->diffForHumans(),
                    'last_lat' => $load->last_lat,
                    'last_lng' => $load->last_lng,
                ];
            })
            ->toArray();
    }
}
