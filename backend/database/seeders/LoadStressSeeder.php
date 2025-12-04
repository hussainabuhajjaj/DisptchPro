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
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LoadStressSeeder extends Seeder
{
    public function run(): void
    {
        $count = 200; // increase/decrease as needed to test scale

        $clients = Client::all();
        $carriers = Carrier::all();
        $drivers = Driver::all();
        $trucks = Truck::all();
        $trailers = Trailer::all();

        // Ensure at least one of each exists
        if ($clients->isEmpty()) {
            $clients = collect([Client::factory()->create()]);
        }
        if ($carriers->isEmpty()) {
            $carriers = collect([Carrier::factory()->create()]);
        }
        if ($drivers->isEmpty()) {
            $drivers = collect([Driver::factory()->create()]);
        }
        if ($trucks->isEmpty()) {
            $trucks = collect([Truck::factory()->create()]);
        }
        if ($trailers->isEmpty()) {
            $trailers = collect([Trailer::factory()->create()]);
        }

        $statuses = ['posted', 'assigned', 'in_transit', 'delivered', 'completed'];

        for ($i = 0; $i < $count; $i++) {
            $client = $clients[$i % $clients->count()];
            $carrier = $carriers[$i % $carriers->count()];
            $driver = $drivers[$i % $drivers->count()];
            $truck = $trucks[$i % $trucks->count()];
            $trailer = $trailers[$i % $trailers->count()];
            $status = $statuses[$i % count($statuses)];

            $pickupDate = Carbon::now()->addDays(random_int(-5, 5));
            $deliveryDate = (clone $pickupDate)->addDays(random_int(1, 4));

            $load = Load::create([
                'load_number' => 'LS-' . Str::upper(Str::random(8)),
                'client_id' => $client->id,
                'carrier_id' => $carrier->id,
                'driver_id' => $driver->id,
                'truck_id' => $truck->id,
                'trailer_id' => $trailer->id,
                'status' => $status,
                'trailer_type' => 'Dry Van',
                'rate_to_client' => random_int(1500, 4500),
                'rate_to_carrier' => random_int(1000, 3200),
                'fuel_surcharge' => random_int(80, 200),
                'distance_miles' => random_int(400, 1600),
                'commodity' => 'Mixed goods',
                'weight' => random_int(20000, 45000),
                'pieces' => random_int(10, 40),
                'equipment_requirements' => '53ft',
            ]);

            LoadStop::create([
                'load_id' => $load->id,
                'sequence' => 1,
                'type' => 'pickup',
                'facility_name' => 'Warehouse ' . ($i + 1),
                'city' => 'City ' . ($i + 1),
                'state' => 'ST',
                'date_from' => $pickupDate,
            ]);

            LoadStop::create([
                'load_id' => $load->id,
                'sequence' => 2,
                'type' => 'delivery',
                'facility_name' => 'DC ' . ($i + 1),
                'city' => 'City ' . ($i + 2),
                'state' => 'ST',
                'date_from' => $deliveryDate,
            ]);

            // Check calls to simulate progress and dwell
            $arrivedPickup = (clone $pickupDate)->addHours(random_int(-2, 1));
            $loaded = (clone $arrivedPickup)->addHours(random_int(2, 6));
            $arrivedDelivery = (clone $deliveryDate)->addHours(random_int(-3, 2));
            $unloaded = (clone $arrivedDelivery)->addHours(random_int(1, 4));

            CheckCall::create([
                'load_id' => $load->id,
                'status' => 'arrived_pickup',
                'reported_at' => $arrivedPickup,
                'note' => 'Arrived pickup',
            ]);
            CheckCall::create([
                'load_id' => $load->id,
                'status' => 'loaded',
                'reported_at' => $loaded,
                'note' => 'Loaded',
            ]);
            if (in_array($status, ['in_transit', 'delivered', 'completed'])) {
                CheckCall::create([
                    'load_id' => $load->id,
                    'status' => 'arrived_delivery',
                    'reported_at' => $arrivedDelivery,
                    'note' => 'Arrived delivery',
                ]);
                CheckCall::create([
                    'load_id' => $load->id,
                    'status' => 'unloaded',
                    'reported_at' => $unloaded,
                    'note' => 'Unloaded',
                ]);
            }

            // Simulated detention if dwell > 2h
            $pickupMinutes = $loaded->diffInMinutes($arrivedPickup);
            if ($pickupMinutes > 120) {
                $hours = round($pickupMinutes / 60, 2);
                $load->accessorial_charges = array_merge($load->accessorial_charges ?? [], [
                    'detention_pickup' => [
                        'label' => 'Detention - Pickup',
                        'minutes' => $pickupMinutes,
                        'hours' => $hours,
                        'revenue' => $hours * 50,
                        'cost' => 0,
                    ],
                ]);
                $load->saveQuietly();
            }
        }
    }
}
