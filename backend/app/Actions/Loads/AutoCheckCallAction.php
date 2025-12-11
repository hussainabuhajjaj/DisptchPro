<?php

namespace App\Actions\Loads;

use App\Models\CheckCall;
use App\Models\Driver;
use App\Models\Load;
use Illuminate\Support\Carbon;

class AutoCheckCallAction
{
    /**
     * Create an arrival check-call when driver is within 0.5km of the next stop.
     */
    public function __invoke(Load $load, Driver $driver, float $lat, float $lng): void
    {
        $stop = $load->stops()
            ->orderBy('sequence')
            ->get()
            ->first(function ($s) {
                return !in_array($s->type, ['delivered', 'completed']);
            });

        if (!$stop || $stop->lat === null || $stop->lng === null) {
            return;
        }

        $distKm = $this->haversineKm($lat, $lng, (float) $stop->lat, (float) $stop->lng);
        if ($distKm === null || $distKm > 0.5) {
            return;
        }

        $status = $stop->type === 'delivery' ? 'arrived_delivery' : 'arrived_pickup';

        $recent = $load->checkCalls()
            ->where('status', $status)
            ->where('reported_at', '>=', Carbon::now()->subHours(2))
            ->exists();

        if ($recent) {
            return;
        }

        CheckCall::create([
            'load_id' => $load->id,
            'user_id' => null,
            'status' => $status,
            'note' => 'Auto check-call via geofence',
            'reported_at' => Carbon::now(),
        ]);
    }

    protected function haversineKm(?float $lat1, ?float $lon1, ?float $lat2, ?float $lon2): ?float
    {
        if ($lat1 === null || $lon1 === null || $lat2 === null || $lon2 === null) {
            return null;
        }
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
