<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('load_stops', function (Blueprint $table) {
            $table->decimal('lat', 10, 6)->nullable()->after('country');
            $table->decimal('lng', 10, 6)->nullable()->after('lat');
        });

        Schema::table('loads', function (Blueprint $table) {
            $table->json('route_polyline')->nullable()->after('internal_notes');
            $table->decimal('route_distance_km', 10, 2)->nullable()->after('route_polyline');
            $table->decimal('route_duration_hr', 10, 2)->nullable()->after('route_distance_km');
        });

        // Backfill coords for existing stops using deterministic lookup
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
            if (isset($coordLookup[$key])) {
                return $coordLookup[$key];
            }
            $hash = crc32($key);
            $lat = 25 + ($hash % 2300000) / 100000;
            $lng = -125 + (($hash >> 8) % 5800000) / 100000;
            return [$lat, $lng];
        };

        DB::table('load_stops')->orderBy('id')->chunkById(500, function ($stops) use ($resolveCoords) {
            foreach ($stops as $stop) {
                if (!is_null($stop->lat) && !is_null($stop->lng)) {
                    continue;
                }
                $coords = $resolveCoords($stop->city, $stop->state);
                if ($coords) {
                    DB::table('load_stops')
                        ->where('id', $stop->id)
                        ->update(['lat' => $coords[0], 'lng' => $coords[1]]);
                }
            }
        });

        // Backfill approximate route distance/duration for loads based on first/last stop
        $haversine = function (array $a, array $b) {
            [$lat1, $lon1] = $a;
            [$lat2, $lon2] = $b;
            $earthRadius = 6371; // km
            $dLat = deg2rad($lat2 - $lat1);
            $dLon = deg2rad($lon2 - $lon1);
            $lat1 = deg2rad($lat1);
            $lat2 = deg2rad($lat2);
            $h = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLon / 2) ** 2;
            return 2 * $earthRadius * asin(min(1, sqrt($h)));
        };

        DB::table('loads')->orderBy('id')->chunkById(200, function ($loads) use ($haversine) {
            foreach ($loads as $load) {
                $stops = DB::table('load_stops')
                    ->where('load_id', $load->id)
                    ->orderBy('sequence')
                    ->get(['lat', 'lng']);
                if ($stops->count() < 2) {
                    continue;
                }
                $first = $stops->first();
                $last = $stops->last();
                if (is_null($first->lat) || is_null($last->lat)) {
                    continue;
                }
                $distanceKm = round($haversine([$first->lat, $first->lng], [$last->lat, $last->lng]), 2);
                $durationHr = $distanceKm ? round($distanceKm / 88.5, 2) : null; // ~55mph -> 88.5km/h
                DB::table('loads')
                    ->where('id', $load->id)
                    ->update([
                        'route_distance_km' => $distanceKm,
                        'route_duration_hr' => $durationHr,
                    ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('load_stops', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });

        Schema::table('loads', function (Blueprint $table) {
            $table->dropColumn(['route_polyline', 'route_distance_km', 'route_duration_hr']);
        });
    }
};
