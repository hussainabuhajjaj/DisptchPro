<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\CheckCall;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Load;
use App\Models\LoadStop;
use App\Models\Trailer;
use App\Models\Truck;
use App\Models\CreditBalance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::factory()->count(3)->create([
            'status' => 'active',
        ]);

        $carriers = Carrier::factory()->count(3)->create();
        $drivers = Driver::factory()->count(3)->create();
        $trucks = Truck::factory()->count(3)->create();
        $trailers = Trailer::factory()->count(3)->create();

        // Credits for first client/carrier to test apply-credit flows
        CreditBalance::firstOrCreate([
            'entity_type' => 'client',
            'entity_id' => $clients->first()->id,
            'source_type' => 'manual',
        ], [
            'amount' => 500,
            'remaining' => 500,
            'reason' => 'Seed client credit',
            'expires_at' => now()->addDays(10),
        ]);

        CreditBalance::firstOrCreate([
            'entity_type' => 'carrier',
            'entity_id' => $carriers->first()->id,
            'source_type' => 'manual',
        ], [
            'amount' => 300,
            'remaining' => 300,
            'reason' => 'Seed carrier credit',
            'expires_at' => now()->addDays(5),
        ]);

        $scenarios = [
            ['status' => 'posted', 'route' => ['Dallas, TX', 'Oklahoma City, OK']],
            ['status' => 'assigned', 'route' => ['Atlanta, GA', 'Charlotte, NC']],
            ['status' => 'in_transit', 'route' => ['Chicago, IL', 'Detroit, MI']],
            ['status' => 'delivered', 'route' => ['Seattle, WA', 'Portland, OR']],
            ['status' => 'completed', 'route' => ['Phoenix, AZ', 'Kansas City, MO']],
            ['status' => 'cancelled', 'route' => ['Denver, CO', 'Salt Lake City, UT']],
        ];

        foreach ($scenarios as $idx => $scenario) {
            $client = $clients[$idx % $clients->count()];
            $carrier = $carriers[$idx % $carriers->count()];
            $driver = $drivers[$idx % $drivers->count()];
            $truck = $trucks[$idx % $trucks->count()];
            $trailer = $trailers[$idx % $trailers->count()];

            $load = Load::create([
                'load_number' => 'L-' . Str::upper(Str::random(6)),
                'client_id' => $client->id,
                'carrier_id' => $carrier->id,
                'driver_id' => $driver->id,
                'truck_id' => $truck->id,
                'trailer_id' => $trailer->id,
                'status' => $scenario['status'],
                'trailer_type' => 'Dry Van',
                'rate_to_client' => 1500 + ($idx * 200),
                'rate_to_carrier' => 1000 + ($idx * 150),
                'fuel_surcharge' => 120,
                'distance_miles' => 500 + ($idx * 80),
                'commodity' => 'Seed freight',
                'weight' => 30000,
                'pieces' => 20,
                'equipment_requirements' => '53ft',
                'accessorial_charges' => [
                    'detention_pickup' => [
                        'label' => 'Detention - Pickup',
                        'minutes' => 150,
                        'hours' => 2.5,
                        'revenue' => 125,
                        'cost' => 0,
                    ],
                ],
            ]);

            // Stops
            LoadStop::create([
                'load_id' => $load->id,
                'sequence' => 1,
                'type' => 'pickup',
                'facility_name' => $scenario['route'][0],
                'city' => $scenario['route'][0],
                'state' => '',
                'date_from' => Carbon::now()->subDays(2),
            ]);
            LoadStop::create([
                'load_id' => $load->id,
                'sequence' => 2,
                'type' => 'delivery',
                'facility_name' => $scenario['route'][1],
                'city' => $scenario['route'][1],
                'state' => '',
                'date_from' => Carbon::now()->addDay(),
            ]);

            // Check calls for dwell/detention testing
            CheckCall::create([
                'load_id' => $load->id,
                'status' => 'arrived_pickup',
                'reported_at' => Carbon::now()->subHours(5),
                'note' => 'Arrived at pickup',
            ]);
            CheckCall::create([
                'load_id' => $load->id,
                'status' => 'loaded',
                'reported_at' => Carbon::now()->subHours(2),
                'note' => 'Loaded and departing',
            ]);
        }
    }
}
