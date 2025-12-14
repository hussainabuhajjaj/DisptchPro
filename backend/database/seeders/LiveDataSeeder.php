<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LiveDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake();

        $carrierId = $this->seedCarrier([
            'name' => 'Hudson Logistics',
            'usd_ot_number' => '1234567',
            'mc_number' => 'MC987654',
            'insurance_primary_name' => 'Allied Insurance',
            'insurance_policy_number' => 'POL-' . $faker->numerify('#######'),
            'insurance_expires_at' => Carbon::now()->addMonths(6),
            'onboarding_status' => 'verified',
            'email' => 'carrier@hadispatch.com',
            'phone' => '+1 973-555-1000',
        ]);

        $driverId = $this->seedDriver([
            'name' => 'Test Driver',
            'email' => 'driver@hadispatch.com',
            'phone' => '+1 201-555-2000',
            'eld_device_id' => 'ELD-' . Str::random(6),
            'eld_vendor' => 'GenericELD',
            'last_active_at' => Carbon::now()->subMinutes(2),
        ]);

        $clientId = $this->seedClient([
            'name' => 'Acme Shippers',
            'email' => 'shipper@acme.com',
            'phone' => '+1 646-555-3000',
        ]);

        $loadId = $this->seedLoad([
            'number' => 'TEST-001',
            'status' => 'en_route',
            'dispatcher_id' => 1,
            'carrier_id' => $carrierId,
            'driver_id' => $driverId,
            'client_id' => $clientId,
            'origin_timezone' => 'America/New_York',
            'destination_timezone' => 'America/New_York',
            'appointment_start' => Carbon::now()->subHours(1),
            'appointment_end' => Carbon::now()->addHours(2),
            'tendered_at' => Carbon::now()->subHours(4),
            'accepted_at' => Carbon::now()->subHours(3),
            'picked_at' => Carbon::now()->subHours(1),
            'rate' => 2200,
            'distance' => 120,
            'last_lat' => 40.7357,
            'last_lng' => -74.1724,
            'last_location_at' => Carbon::now()->subMinutes(2),
        ]);

        $this->seedStops($loadId);
        $this->seedCheckCalls($loadId, $driverId);
        $this->seedLocations($loadId, $driverId);
    }

    private function seedCarrier(array $data): int
    {
        return $this->insertOrFirst('carriers', 'name', $data);
    }

    private function seedDriver(array $data): int
    {
        return $this->insertOrFirst('drivers', 'email', $data);
    }

    private function seedClient(array $data): int
    {
        return $this->insertOrFirst('clients', 'email', $data);
    }

    private function seedLoad(array $data): int
    {
        return $this->insertOrFirst('loads', 'number', $data);
    }

    private function seedStops(int $loadId): void
    {
        $stops = [
            [
                'load_id' => $loadId,
                'type' => 'pickup',
                'sequence' => 1,
                'name' => 'Newark Warehouse',
                'city' => 'Newark',
                'state' => 'NJ',
                'date_from' => Carbon::now()->subHours(1),
                'date_to' => Carbon::now()->addMinutes(30),
                'lat' => 40.7357,
                'lng' => -74.1724,
            ],
            [
                'load_id' => $loadId,
                'type' => 'delivery',
                'sequence' => 2,
                'name' => 'Brooklyn DC',
                'city' => 'Brooklyn',
                'state' => 'NY',
                'date_from' => Carbon::now()->addHours(1),
                'date_to' => Carbon::now()->addHours(3),
                'lat' => 40.6782,
                'lng' => -73.9442,
            ],
        ];

        foreach ($stops as $stop) {
            $this->insertIfMissing('load_stops', ['load_id' => $stop['load_id'], 'sequence' => $stop['sequence']], $stop);
        }
    }

    private function seedCheckCalls(int $loadId, int $driverId): void
    {
        $events = [
            ['event_code' => 'ARRIVED_PICKUP', 'reported_at' => Carbon::now()->subHours(1)],
            ['event_code' => 'LOADED', 'reported_at' => Carbon::now()->subMinutes(50)],
            ['event_code' => 'ENROUTE', 'reported_at' => Carbon::now()->subMinutes(30)],
        ];

        foreach ($events as $event) {
            $data = [
                'load_id' => $loadId,
                'driver_id' => $driverId,
                'note' => 'Auto-seeded check call',
                'reported_at' => $event['reported_at'],
                'event_code' => $event['event_code'],
            ];

            $this->insertIfMissing('check_calls', ['load_id' => $loadId, 'event_code' => $event['event_code']], $data);
        }
    }

    private function seedLocations(int $loadId, int $driverId): void
    {
        $now = Carbon::now();
        $points = [
            ['lat' => 40.7357, 'lng' => -74.1724, 'speed' => 32, 'recorded_at' => $now->copy()->subMinutes(5)],
            ['lat' => 40.7000, 'lng' => -74.0000, 'speed' => 38, 'recorded_at' => $now->copy()->subMinutes(3)],
            ['lat' => 40.6782, 'lng' => -73.9442, 'speed' => 12, 'recorded_at' => $now->copy()->subMinute()],
        ];

        foreach ($points as $point) {
            $data = [
                'load_id' => $loadId,
                'driver_id' => $driverId,
                'lat' => $point['lat'],
                'lng' => $point['lng'],
                'speed' => $point['speed'],
                'heading' => 90,
                'recorded_at' => $point['recorded_at'],
                'source' => 'driver_app',
            ];

            $this->insertIfMissing('load_locations', ['load_id' => $loadId, 'recorded_at' => $point['recorded_at']], $data);
        }
    }

    private function insertOrFirst(string $table, string $uniqueColumn, array $data): int
    {
        $filtered = $this->filterColumns($table, $data);
        $existing = DB::table($table)->where($uniqueColumn, $filtered[$uniqueColumn] ?? null)->first();
        if ($existing) {
            return $existing->id;
        }

        return DB::table($table)->insertGetId($filtered);
    }

    private function insertIfMissing(string $table, array $uniqueKeys, array $data): void
    {
        $filtered = $this->filterColumns($table, $data);
        $query = DB::table($table);
        foreach ($uniqueKeys as $key => $value) {
            $query->where($key, $value);
        }

        if (!$query->exists()) {
            DB::table($table)->insert($filtered);
        }
    }

    private function filterColumns(string $table, array $data): array
    {
        if (!Schema::hasTable($table)) {
            return [];
        }

        $columns = Schema::getColumnListing($table);

        return collect($data)
            ->filter(fn ($_, $key) => in_array($key, $columns, true))
            ->all();
    }
}
