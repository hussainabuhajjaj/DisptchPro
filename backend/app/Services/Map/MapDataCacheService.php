<?php

namespace App\Services\Map;

use App\Models\Load;
use Illuminate\Support\Facades\Cache;

class MapDataCacheService
{
    public const CACHE_KEY = 'map:active-loads';
    public const TTL_SECONDS = 30;

    /**
     * Return cached payload of active loads with exception flags.
     */
    public function get(): array
    {
        return Cache::remember(self::CACHE_KEY, self::TTL_SECONDS, function () {
            $loads = Load::query()
                ->with([
                    'driver',
                    'dispatcher',
                    'stops' => fn ($q) => $q->orderBy('sequence'),
                    'checkCalls' => fn ($q) => $q->latest('reported_at')->limit(1),
                    'locations' => fn ($q) => $q->latest('recorded_at')->limit(15),
                ])
                ->whereNotIn('status', ['delivered', 'completed', 'cancelled'])
                ->whereNotNull('last_lat')
                ->whereNotNull('last_lng')
                ->limit(200)
                ->get();

            // Precompute driver conflicts
            $driverCounts = $loads->groupBy('driver_id')->map->count();

            $payload = $loads->map(function (Load $load) use ($driverCounts) {
                $lat = $load->last_lat ? (float) $load->last_lat : null;
                $lng = $load->last_lng ? (float) $load->last_lng : null;
                $finalStop = $load->stops->where('type', 'delivery')->sortBy('sequence')->last();

                $noRecentPing = $load->last_location_at
                    ? $load->last_location_at->lt(now()->subMinutes(10))
                    : true;

                $isLate = false;
                if ($load->last_eta_minutes && $finalStop && $finalStop->date_from) {
                    $etaArrival = now()->addMinutes($load->last_eta_minutes);
                    $scheduled = $finalStop->date_from instanceof \Illuminate\Support\Carbon
                        ? $finalStop->date_from
                        : \Illuminate\Support\Carbon::parse($finalStop->date_from);
                    $isLate = $etaArrival->greaterThan($scheduled);
                }

                $noRecentCheckCall = $this->noRecentCheckCall($load);
                $conflict = $driverCounts[$load->driver_id] ?? 0;
                $hasDriverConflict = $conflict > 1;

                return [
                    'id' => $load->id,
                    'load_number' => $load->load_number,
                    'status' => $load->status,
                    'route_status' => $load->route_status,
                    'dispatcher' => $load->dispatcher?->name,
                    'driver' => $load->driver?->name,
                    'last_lat' => $lat,
                    'last_lng' => $lng,
                    'last_location_at' => $load->last_location_at?->toIso8601String(),
                    'eta_minutes' => $load->last_eta_minutes,
                    'flags' => [
                        'late_eta' => $isLate,
                        'no_recent_ping' => $noRecentPing,
                        'no_recent_check_call' => $noRecentCheckCall,
                        'driver_conflict' => $hasDriverConflict,
                    ],
                    'stops' => $load->stops->map(fn ($s) => [
                        'id' => $s->id,
                        'type' => $s->type,
                        'lat' => $s->lat ? (float) $s->lat : null,
                        'lng' => $s->lng ? (float) $s->lng : null,
                        'city' => $s->city,
                        'state' => $s->state,
                        'date_from' => optional($s->date_from)?->toIso8601String(),
                    ])->values(),
                    'breadcrumbs' => $load->locations
                        ->sortByDesc('recorded_at')
                        ->map(fn ($loc) => [
                            'lat' => $loc->lat,
                            'lng' => $loc->lng,
                            'recorded_at' => optional($loc->recorded_at)?->toIso8601String(),
                        ])
                        ->values(),
                ];
            })->values();

            return ['loads' => $payload];
        });
    }

    public function bust(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected function noRecentCheckCall(Load $load): bool
    {
        $last = optional($load->checkCalls->first())->reported_at;
        if (!$last) {
            return true;
        }
        return $last->lt(now()->subHours(12));
    }
}
