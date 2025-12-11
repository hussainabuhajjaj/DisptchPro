<?php

namespace App\Actions\Drivers;

use App\Actions\Loads\AutoCheckCallAction;
use App\Models\Driver;
use App\Models\Load;
use App\Models\LoadLocation;
use App\Services\Geo\RouteService;
use App\Services\Map\MapDataCacheService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class RecordLocationAction
{
    public function __construct(
        protected RouteService $routeService,
        protected AutoCheckCallAction $autoCheckCallAction,
        protected MapDataCacheService $mapCache
    ) {
    }

    public function execute(Driver $driver, Load $load, array $payload): array
    {
        $lat = (float) $payload['lat'];
        $lng = (float) $payload['lng'];
        $speed = isset($payload['speed']) ? (float) $payload['speed'] : null;
        $heading = $payload['heading'] ?? null;
        $accuracy = $payload['accuracy_m'] ?? null;
        $source = $payload['source'] ?? 'gps';
        $trackId = $payload['track_id'] ?? null;
        $recordedAt = isset($payload['recorded_at'])
            ? Carbon::parse($payload['recorded_at'])
            : Carbon::now();

        if ($accuracy !== null && $accuracy > 5000) {
            return [
                'ignored' => true,
                'reason' => 'Accuracy too low to store location',
            ];
        }

        // Reject out-of-order or unrealistic jumps (basic guard)
        if ($load->last_lat && $load->last_lng && $load->last_location_at) {
            $seconds = max(1, $recordedAt->diffInSeconds($load->last_location_at));
            $isJump = $this->routeService->isJumpUnrealistic(
                (float) $load->last_lat,
                (float) $load->last_lng,
                $lat,
                $lng,
                max(1, $seconds)
            );
            if ($isJump) {
                return [
                    'ignored' => true,
                    'reason' => 'Unrealistic jump filtered',
                ];
            }

            // Basic smoothing: if accuracy is poor or we are within 60 seconds, average with previous point.
            if (($accuracy !== null && $accuracy > 150) || $seconds < 60) {
                $lat = ($lat + (float) $load->last_lat) / 2;
                $lng = ($lng + (float) $load->last_lng) / 2;
            }
        }

        $location = LoadLocation::create([
            'load_id' => $load->id,
            'driver_id' => $driver->id,
            'lat' => $lat,
            'lng' => $lng,
            'speed' => $speed,
            'heading' => $heading,
            'accuracy_m' => $accuracy,
            'source' => $source,
            'track_id' => $trackId,
            'recorded_at' => $recordedAt,
            'is_valid' => true,
        ]);

        $etaMinutes = $this->routeService->computeEtaMinutes($load, $lat, $lng, $speed);

        $load->forceFill([
            'last_lat' => $lat,
            'last_lng' => $lng,
            'last_location_at' => $recordedAt,
            'last_eta_minutes' => $etaMinutes,
        ])->save();

        // Geofence auto check-call
        ($this->autoCheckCallAction)($load, $driver, $lat, $lng);

        // Optionally cache map payload invalidation
        Cache::forget("map:load:{$load->id}");
        $this->mapCache->bust();

        return [
            'location_id' => $location->id,
            'eta_minutes' => $etaMinutes,
        ];
    }
}
