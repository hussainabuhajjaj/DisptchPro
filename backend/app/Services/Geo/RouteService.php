<?php

namespace App\Services\Geo;

use App\Models\Load;

class RouteService
{
    public function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Compute ETA in minutes from current position to final delivery stop.
     * Applies simple smoothing and speed clamping.
     */
    public function computeEtaMinutes(Load $load, float $lat, float $lng, ?float $speedKph = null): ?int
    {
        $final = $load->stops()
            ->where('type', 'delivery')
            ->orderBy('sequence')
            ->get()
            ->last();

        if (!$final || $final->lat === null || $final->lng === null) {
            return null;
        }

        $distanceKm = $this->haversineKm($lat, $lng, (float) $final->lat, (float) $final->lng);

        // If speed not provided, fall back to planned speed or 70kph.
        $avgPlannedSpeed = ($load->route_distance_km && $load->route_duration_hr)
            ? max(10, $load->route_distance_km / max(0.1, $load->route_duration_hr))
            : 70;

        $speed = $speedKph ?? $avgPlannedSpeed;

        // Clamp extremes to avoid bad ETA from spikes.
        $speed = max(5, min($speed, 120));

        $etaHours = $speed > 0 ? ($distanceKm / $speed) : null;

        return $etaHours ? (int) round($etaHours * 60) : null;
    }

    /**
     * Validate a jump between two coordinates to detect outliers.
     */
    public function isJumpUnrealistic(
        float $prevLat,
        float $prevLng,
        float $newLat,
        float $newLng,
        int $secondsBetween,
        float $maxSpeedKph = 150
    ): bool {
        if ($secondsBetween <= 0) {
            return false;
        }
        $dist = $this->haversineKm($prevLat, $prevLng, $newLat, $newLng);
        $hours = $secondsBetween / 3600;
        $speed = $hours > 0 ? ($dist / $hours) : 0;

        return $speed > $maxSpeedKph;
    }
}
