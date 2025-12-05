<?php

namespace App\Filament\Pages;

use App\Models\Load;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use UnitEnum;

class TransportBoard extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-map';
    protected static UnitEnum|string|null $navigationGroup = 'Operations';
    protected static ?string $title = 'TMS Map';
    protected string $view = 'filament.pages.transport-board';

    protected function getViewData(): array
    {
        return [
            'loads' => $this->getLoadsForMap(),
        ];
    }

    public static function mapData(): array
    {
        /** @var self $page */
        $page = app(static::class);
        // Use file cache to avoid DB cache size limits for large polylines.
        return Cache::store('file')->remember('tms-map-data', 30, fn () => $page->getLoadsForMap()->all());
    }

    protected static function haversine(array $a, array $b): float
    {
        [$lat1, $lon1] = $a;
        [$lat2, $lon2] = $b;
        $earthRadius = 3958.8; // miles
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);
        $h = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLon / 2) ** 2;
        return 2 * $earthRadius * asin(min(1, sqrt($h)));
    }

    protected function getLoadsForMap()
    {
        $coordLookup = [
            'dallas,tx' => [32.7767, -96.7970],
            'kansas city,mo' => [39.0997, -94.5786],
            'chicago,il' => [41.8781, -87.6298],
            'phoenix,az' => [33.4484, -112.0740],
            'atlanta,ga' => [33.7490, -84.3880],
            'los angeles,ca' => [34.0522, -118.2437],
            'houston,tx' => [29.7604, -95.3698],
            'seattle,wa' => [47.6062, -122.3321],
            'denver,co' => [39.7392, -104.9903],
            'charlotte,nc' => [35.2271, -80.8431],
            'new york,ny' => [40.7128, -74.0060],
            'miami,fl' => [25.7617, -80.1918],
            'boston,ma' => [42.3601, -71.0589],
            'minneapolis,mn' => [44.9778, -93.2650],
        ];

        $resolveCoords = function (?string $city, ?string $state) use ($coordLookup) {
            if (!$city || !$state) {
                return null;
            }
            $key = strtolower(trim($city) . ',' . trim($state));
            return Cache::remember("geo:$key", 86400, function () use ($coordLookup, $key) {
                if (isset($coordLookup[$key])) {
                    return $coordLookup[$key];
                }
                // Deterministic pseudo coords for unknown cities (spread across US bounds)
                $hash = crc32($key);
                $lat = 25 + ($hash % 2300000) / 100000; // roughly 25 to 48
                $lng = -125 + (($hash >> 8) % 5800000) / 100000; // roughly -125 to -67
                return [$lat, $lng];
            });
        };

        $routeCache = function (array $coordsList) {
            if (count($coordsList) < 2) {
                return null;
            }
            $key = 'route:' . md5(json_encode($coordsList));
            return Cache::remember($key, 900, function () use ($coordsList) {
                try {
                    $url = 'https://router.project-osrm.org/route/v1/driving/' . collect($coordsList)
                        ->map(fn ($c) => $c[1] . ',' . $c[0]) // lng,lat
                        ->join(';') . '?overview=full&geometries=geojson';
                    $resp = Http::timeout(6)->get($url);
                    if (!$resp->ok()) {
                        return null;
                    }
                    $data = $resp->json();
                    $route = $data['routes'][0] ?? null;
                    if (!$route || empty($route['geometry']['coordinates'])) {
                        return null;
                    }
                    $poly = collect($route['geometry']['coordinates'])->map(fn ($pair) => [$pair[1], $pair[0]])->all();
                    return [
                        'polyline' => $poly,
                        'distance_km' => round($route['distance'] / 1000, 1),
                        'duration_hr' => round($route['duration'] / 3600, 1),
                    ];
                } catch (\Throwable $e) {
                    return null;
                }
            });
        };

        return Load::with(['client', 'carrier', 'driver', 'dispatcher', 'stops', 'checkCalls'])
            ->orderByDesc('id')
            ->take(25)
            ->get()
            ->map(function ($load) use ($resolveCoords, $routeCache) {
                $lastCall = $load->checkCalls()->latest('reported_at')->first();
                $accessorials = $load->accessorial_charges ?? [];
                $accessorialTotal = collect($accessorials)->sum(fn ($a) => $a['revenue'] ?? 0);
                $detentionPickup = $accessorials['detention_pickup']['minutes'] ?? 0;
                $detentionDelivery = $accessorials['detention_delivery']['minutes'] ?? 0;
                $detentionPickupHours = $accessorials['detention_pickup']['hours'] ?? ($detentionPickup ? round($detentionPickup / 60, 2) : 0);
                $detentionDeliveryHours = $accessorials['detention_delivery']['hours'] ?? ($detentionDelivery ? round($detentionDelivery / 60, 2) : 0);
                $slaFlags = [];
                if ($detentionPickup) {
                    $slaFlags[] = 'Pickup detention';
                }
                if ($detentionDelivery) {
                    $slaFlags[] = 'Delivery detention';
                }
                $stops = $load->stops->map(function ($stop) use ($resolveCoords) {
                    $coords = $stop->lat && $stop->lng ? [$stop->lat, $stop->lng] : $resolveCoords($stop->city, $stop->state);
                    return [
                        'city' => $stop->city,
                        'state' => $stop->state,
                        'type' => $stop->type,
                        'sequence' => $stop->sequence,
                        'date' => optional($stop->date_from)->toDateString(),
                        'coords' => $coords,
                    ];
                })->filter(fn ($stop) => !empty($stop['coords']))->values();

                $pickup = $stops->sortBy('sequence')->first();
                $drop = $stops->sortByDesc('sequence')->first();
                $lane = $pickup && $drop
                    ? ($pickup['city'] . ', ' . $pickup['state'] . ' → ' . $drop['city'] . ', ' . $drop['state'])
                    : '—';
                $truckPosition = null;
                if ($pickup && $drop && $pickup !== $drop) {
                    if (in_array($load->status, ['delivered', 'completed'])) {
                        $truckPosition = $drop['coords'];
                    } elseif ($load->status === 'in_transit') {
                        $truckPosition = [
                            ($pickup['coords'][0] + $drop['coords'][0]) / 2,
                            ($pickup['coords'][1] + $drop['coords'][1]) / 2,
                        ];
                    } else {
                        $truckPosition = $pickup['coords'];
                    }
                } elseif ($pickup) {
                    $truckPosition = $pickup['coords'];
                }

                $startDate = $stops->pluck('date')->filter()->min();
                $endDate = $stops->pluck('date')->filter()->max();
                // Prefer delivery stop date_to if present for tighter SLA windows.
                $deliveryDateTo = $load->stops
                    ->where('type', 'delivery')
                    ->map(fn ($stop) => optional($stop->date_to)->toDateString())
                    ->filter()
                    ->max();
                if ($deliveryDateTo) {
                    $endDate = $deliveryDateTo;
                }

                $distanceMiles = null;
                $etaHours = null;
                $routePolyline = $load->route_polyline;
                $routeDistanceKm = $load->route_distance_km;
                $routeDurationHr = $load->route_duration_hr;
                if ($pickup && $drop) {
                    $distanceMiles = round(self::haversine($pickup['coords'], $drop['coords']), 1);
                    $etaHours = $distanceMiles ? round($distanceMiles / 55, 1) : null; // rough 55 mph
                    if (!$routePolyline) {
                        $route = $routeCache([$pickup['coords'], $drop['coords']]);
                        if ($route) {
                            $routePolyline = $route['polyline'] ?? null;
                            $routeDistanceKm = $route['distance_km'] ?? null;
                            $routeDurationHr = $route['duration_hr'] ?? null;
                            if ($routeDistanceKm) {
                                $distanceMiles = round($routeDistanceKm * 0.621371, 1);
                            }
                            if ($routeDurationHr) {
                                $etaHours = $routeDurationHr;
                            }
                            // Persist for reuse
                            try {
                                $load->updateQuietly([
                                    'route_polyline' => $routePolyline,
                                    'route_distance_km' => $routeDistanceKm,
                                    'route_duration_hr' => $routeDurationHr,
                                ]);
                            } catch (\Throwable $e) {
                                // ignore write failures
                                
                            }
                        }
                    }
                }

                $now = now()->toDateString();
                $isLate = false;
                $isAtRisk = false;
                // Prefer model accessor if available; otherwise compute from stops.
                $routeStatus = $load->route_status ?? null;
                if (!$routeStatus || $routeStatus === 'on_time') {
                    $isLate = $endDate && $endDate < $now && !in_array($load->status, ['delivered', 'completed']);
                    $isAtRisk = $endDate && !$isLate && !in_array($load->status, ['delivered', 'completed']) && $endDate <= now()->addHours(6)->toDateString();
                    $routeStatus = $isLate ? 'late' : ($isAtRisk ? 'at_risk' : 'on_time');
                } else {
                    $isLate = $routeStatus === 'late';
                    $isAtRisk = $routeStatus === 'at_risk';
                }
                if ($isLate) {
                    $slaFlags[] = 'Late delivery window';
                } elseif ($isAtRisk) {
                    $slaFlags[] = 'Delivery window near';
                }

                return [
                    'id' => $load->id,
                    'load_number' => $load->load_number,
                    'status' => $load->status,
                    'client' => $load->client?->name,
                    'carrier' => $load->carrier?->name,
                    'driver' => $load->driver?->name,
                    'dispatcher' => $load->dispatcher?->name,
                    'dispatcher_id' => $load->dispatcher_id,
                    'lane' => $lane,
                    'stops' => $stops,
                    'truck_position' => $truckPosition,
                    'notes' => $load->internal_notes,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'edit_url' => route('filament.admin.resources.loads.edit', $load),
                    'distance_miles' => $distanceMiles,
                    'eta_hours' => $etaHours,
                    'route_status' => $routeStatus,
                    'route_polyline' => $routePolyline,
                    'route_distance_km' => $routeDistanceKm,
                    'route_duration_hr' => $routeDurationHr,
                    'last_event' => $lastCall?->status,
                    'last_event_time' => $lastCall?->reported_at?->toDateTimeString(),
                    'accessorial_total' => $accessorialTotal,
                    'sla_flags' => $slaFlags,
                    'detention_pickup_minutes' => $detentionPickup,
                    'detention_delivery_minutes' => $detentionDelivery,
                    'detention_pickup_hours' => $detentionPickupHours,
                    'detention_delivery_hours' => $detentionDeliveryHours,
                ];
            })
            ->values();
    }
}
