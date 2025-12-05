<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Load;
use App\Models\Trailer;
use App\Models\Truck;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LoadSeeder extends Seeder
{
    public function run(): void
    {
        $dispatcher = User::first() ?? User::factory()->create([
            'name' => 'Dispatch Lead',
            'email' => 'dispatch@example.com',
        ]);
        $clients = Client::all()->take(5);
        $carriers = Carrier::all()->take(5);
        $drivers = Driver::all()->take(5);
        $trucks = Truck::all()->take(5);
        $trailers = Trailer::all()->take(5);

        if ($clients->isEmpty()) {
            return;
        }

        $lanes = [
            ['from' => ['city' => 'Chicago', 'state' => 'IL', 'lat' => 41.8781, 'lng' => -87.6298], 'to' => ['city' => 'Indianapolis', 'state' => 'IN', 'lat' => 39.7684, 'lng' => -86.1581], 'equipment' => '53ft Dry Van'],
            ['from' => ['city' => 'Dallas', 'state' => 'TX', 'lat' => 32.7767, 'lng' => -96.7970], 'to' => ['city' => 'Atlanta', 'state' => 'GA', 'lat' => 33.7490, 'lng' => -84.3880], 'equipment' => 'Reefer 36F'],
            ['from' => ['city' => 'Phoenix', 'state' => 'AZ', 'lat' => 33.4484, 'lng' => -112.0740], 'to' => ['city' => 'Denver', 'state' => 'CO', 'lat' => 39.7392, 'lng' => -104.9903], 'equipment' => 'Flatbed w/ straps'],
            ['from' => ['city' => 'Seattle', 'state' => 'WA', 'lat' => 47.6062, 'lng' => -122.3321], 'to' => ['city' => 'Portland', 'state' => 'OR', 'lat' => 45.5051, 'lng' => -122.6750], 'equipment' => '53ft Dry Van'],
            ['from' => ['city' => 'Miami', 'state' => 'FL', 'lat' => 25.7617, 'lng' => -80.1918], 'to' => ['city' => 'Charlotte', 'state' => 'NC', 'lat' => 35.2271, 'lng' => -80.8431], 'equipment' => 'Reefer 34F'],
            ['from' => ['city' => 'Los Angeles', 'state' => 'CA', 'lat' => 34.0522, 'lng' => -118.2437], 'to' => ['city' => 'Salt Lake City', 'state' => 'UT', 'lat' => 40.7608, 'lng' => -111.8910], 'equipment' => 'Flatbed w/ tarps'],
        ];

        $statuses = ['draft', 'posted', 'assigned', 'in_transit', 'delivered'];
        $commodities = ['Packaged foods', 'Electronics', 'Machinery', 'Produce', 'Paper goods', 'Building materials'];

        foreach ($lanes as $index => $lane) {
            $start = Carbon::now()->addDays($index - 2);
            $end = $start->copy()->addDays(rand(1, 4));
            $distanceMiles = round($this->haversine([$lane['from']['lat'], $lane['from']['lng']], [$lane['to']['lat'], $lane['to']['lng']]) * 0.621371, 0);
            $status = $statuses[$index % count($statuses)];
            $ref = 'REF-' . Str::upper(Str::random(5));

            $load = Load::create([
                'load_number' => 'LD-' . Str::upper(Str::random(6)),
                'client_id' => $clients->random()->id,
                'carrier_id' => optional($carriers->random())->id,
                'driver_id' => optional($drivers->random())->id,
                'truck_id' => optional($trucks->random())->id,
                'trailer_id' => optional($trailers->random())->id,
                'dispatcher_id' => $dispatcher?->id,
                'status' => $status,
                'trailer_type' => $lane['equipment'],
                'rate_to_client' => rand(2800, 5200),
                'rate_to_carrier' => rand(2000, 3800),
                'fuel_surcharge' => rand(120, 260),
                'distance_miles' => $distanceMiles,
                'estimated_distance' => $distanceMiles ? $distanceMiles + rand(10, 80) : null,
                'commodity' => $commodities[array_rand($commodities)],
                'weight' => rand(18000, 42000),
                'pieces' => rand(10, 32),
                'equipment_requirements' => $lane['equipment'],
                'reference_numbers' => ['bol' => $ref, 'ref' => $ref],
                'accessorial_charges' => [
                    'fuel' => ['label' => 'Fuel surcharge', 'revenue' => rand(80, 220), 'cost' => rand(60, 140)],
                    'detention_pickup' => ['label' => 'Detention pickup', 'revenue' => rand(0, 120), 'cost' => rand(0, 50)],
                ],
                'driver_notes' => 'Call 1h before arrival.',
                'internal_notes' => 'Seeded realistic lane',
                'route_distance_km' => $distanceMiles ? round($distanceMiles * 1.60934, 1) : null,
                'route_duration_hr' => $distanceMiles ? round($distanceMiles / 55, 1) : null,
            ]);

            $load->stops()->createMany([
                [
                    'sequence' => 1,
                    'type' => 'pickup',
                    'facility_name' => "{$lane['from']['city']} Warehouse",
                    'city' => $lane['from']['city'],
                    'state' => $lane['from']['state'],
                    'country' => 'US',
                    'address' => '123 Industrial Rd',
                    'zip' => '00000',
                    'contact_person' => 'Dock Lead',
                    'contact_phone' => '555-111-2222',
                    'appointment_time' => '08:00',
                    'lat' => $lane['from']['lat'],
                    'lng' => $lane['from']['lng'],
                    'date_from' => $start->copy()->setTime(8, 0),
                    'date_to' => $start->copy()->setTime(10, 0),
                    'instructions' => 'Check in at gate 2.',
                ],
                [
                    'sequence' => 2,
                    'type' => 'delivery',
                    'facility_name' => "{$lane['to']['city']} DC",
                    'city' => $lane['to']['city'],
                    'state' => $lane['to']['state'],
                    'country' => 'US',
                    'address' => '789 Logistics Ave',
                    'zip' => '00000',
                    'contact_person' => 'Receiving',
                    'contact_phone' => '555-333-4444',
                    'appointment_time' => '09:00',
                    'lat' => $lane['to']['lat'],
                    'lng' => $lane['to']['lng'],
                    'date_from' => $end->copy()->setTime(9, 0),
                    'date_to' => $end->copy()->setTime(11, 0),
                    'instructions' => 'Use door 12 for unload.',
                ],
            ]);
        }
    }

    protected function haversine(array $a, array $b): float
    {
        [$lat1, $lon1] = $a;
        [$lat2, $lon2] = $b;
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);
        $h = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLon / 2) ** 2;
        return 2 * $earthRadius * asin(min(1, sqrt($h)));
    }
}
