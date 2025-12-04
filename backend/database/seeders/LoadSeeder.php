<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Load;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LoadSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::first();
        $carrier = Carrier::first();
        $driver = Driver::first();

        if (! $client) {
            return;
        }

        $loads = [
            [
                'load_number' => 'L-' . Str::upper(Str::random(6)),
                'client_id' => $client->id,
                'carrier_id' => $carrier?->id,
                'driver_id' => $driver?->id,
                'status' => 'posted',
                'trailer_type' => 'Dry Van',
                'rate_to_client' => 3200,
                'rate_to_carrier' => 2200,
                'fuel_surcharge' => 150,
                'distance_miles' => 900,
                'commodity' => 'Packaged foods',
                'weight' => 32000,
                'pieces' => 26,
                'equipment_requirements' => '53ft',
            ],
            [
                'load_number' => 'L-' . Str::upper(Str::random(6)),
                'client_id' => $client->id,
                'carrier_id' => $carrier?->id,
                'driver_id' => $driver?->id,
                'status' => 'assigned',
                'trailer_type' => 'Reefer',
                'rate_to_client' => 4100,
                'rate_to_carrier' => 3000,
                'fuel_surcharge' => 200,
                'distance_miles' => 1200,
                'commodity' => 'Produce',
                'weight' => 38000,
                'pieces' => 30,
                'equipment_requirements' => 'Reefer 36F',
            ],
            [
                'load_number' => 'L-' . Str::upper(Str::random(6)),
                'client_id' => $client->id,
                'carrier_id' => $carrier?->id,
                'driver_id' => $driver?->id,
                'status' => 'in_transit',
                'trailer_type' => 'Flatbed',
                'rate_to_client' => 2800,
                'rate_to_carrier' => 1900,
                'fuel_surcharge' => 120,
                'distance_miles' => 700,
                'commodity' => 'Machinery',
                'weight' => 25000,
                'pieces' => 12,
                'equipment_requirements' => 'Chains/straps',
            ],
        ];

        foreach ($loads as $data) {
            Load::firstOrCreate(
                ['load_number' => $data['load_number']],
                $data
            );
        }
    }
}
