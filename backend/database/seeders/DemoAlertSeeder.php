<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Client;
use App\Models\Carrier;
use App\Models\Driver;
use App\Models\Truck;
use App\Models\Trailer;
use App\Models\Load;

class DemoAlertSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure we have a dispatcher for notifications.
        $dispatcher = User::first() ?? User::factory()->create([
            'name' => 'Demo Dispatcher',
            'email' => 'dispatcher@example.com',
        ]);

        $client = Client::first() ?? Client::factory()->create(['name' => 'Demo Client']);
        $carrier = Carrier::first() ?? Carrier::factory()->create(['name' => 'Demo Carrier']);
        $driver = Driver::first() ?? Driver::factory()->create(['name' => 'Demo Driver']);
        $truck = Truck::first() ?? Truck::factory()->create(['unit_number' => 'TRK-' . random_int(100, 999)]);
        $trailer = Trailer::first() ?? Trailer::factory()->create(['trailer_number' => 'TRL-' . random_int(100, 999)]);

        $loads = [
            [
                'label' => 'Late load triggers alert + Slack',
                'status' => 'in_transit',
                'delivery_at' => Carbon::now()->subHours(2),
            ],
            [
                'label' => 'At-risk (within 6h) triggers warning',
                'status' => 'in_transit',
                'delivery_at' => Carbon::now()->addHours(4),
            ],
            [
                'label' => 'Healthy load for contrast',
                'status' => 'assigned',
                'delivery_at' => Carbon::now()->addDays(2),
            ],
        ];

        foreach ($loads as $idx => $meta) {
            $load = Load::factory()->create([
                'load_number' => 'DEMO-' . Str::upper(Str::random(5)) . "-{$idx}",
                'client_id' => $client->id,
                'carrier_id' => $carrier->id,
                'driver_id' => $driver->id,
                'truck_id' => $truck->id,
                'trailer_id' => $trailer->id,
                'dispatcher_id' => $dispatcher->id,
                'status' => $meta['status'],
                'distance_miles' => 900,
                'rate_to_client' => 3200,
                'rate_to_carrier' => 2400,
                'fuel_surcharge' => 180,
                'internal_notes' => $meta['label'],
            ]);

            // Minimal pickup/delivery stops so SLA status works in UI.
            $load->stops()->createMany([
                [
                    'sequence' => 1,
                    'type' => 'pickup',
                    'facility_name' => 'Demo Pickup DC',
                    'city' => 'Chicago',
                    'state' => 'IL',
                    'country' => 'US',
                    'date_from' => Carbon::now()->subDay(),
                    'date_to' => Carbon::now()->subDay()->addHours(2),
                ],
                [
                    'sequence' => 2,
                    'type' => 'delivery',
                    'facility_name' => 'Demo Delivery DC',
                    'city' => 'Indianapolis',
                    'state' => 'IN',
                    'country' => 'US',
                    'date_from' => $meta['delivery_at'],
                    'date_to' => $meta['delivery_at']->copy()->addHours(2),
                ],
            ]);
        }
    }
}
