<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Carrier;
use App\Models\Driver;
use App\Models\Load;
use App\Models\LoadLocation;
use App\Models\LoadStop;
use App\Models\User;

class TestLiveMapSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::firstOrCreate(
            ['name' => 'Test Client'],
            ['phone' => '555-1000', 'email' => 'client@example.com', 'city' => 'New York', 'state' => 'NY']
        );

        $carrier = Carrier::firstOrCreate(
            ['name' => 'Test Carrier'],
            ['phone' => '555-2000', 'email' => 'carrier@example.com', 'city' => 'Newark', 'state' => 'NJ']
        );

        $dispatcher = User::first();

        $driver = Driver::updateOrCreate(
            ['phone' => '5550001111'],
            [
                'name' => 'Test Driver',
                'carrier_id' => $carrier->id,
                'email' => 'driver@example.com',
                'password' => 'password',
                'status' => 'active',
                'api_token' => 'driver-test-token',
                'api_token_expires_at' => now()->addDays(30),
            ]
        );

        $load = Load::updateOrCreate(
            ['load_number' => 'TEST-001'],
            [
                'client_id' => $client->id,
                'carrier_id' => $carrier->id,
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher?->id,
                'status' => 'in_transit',
                'commodity' => 'Sample Goods',
                'last_lat' => 40.7128,
                'last_lng' => -74.0060,
                'last_location_at' => now(),
                'last_eta_minutes' => 45,
            ]
        );

        // Seed two simple stops
        LoadStop::updateOrCreate(
            ['load_id' => $load->id, 'sequence' => 1],
            [
                'type' => 'pickup',
                'facility_name' => 'Origin DC',
                'city' => 'Newark',
                'state' => 'NJ',
                'lat' => 40.7357,
                'lng' => -74.1724,
                'date_from' => now()->subHours(1),
            ]
        );

        LoadStop::updateOrCreate(
            ['load_id' => $load->id, 'sequence' => 2],
            [
                'type' => 'delivery',
                'facility_name' => 'Destination Warehouse',
                'city' => 'Brooklyn',
                'state' => 'NY',
                'lat' => 40.6782,
                'lng' => -73.9442,
                'date_from' => now()->addHours(6),
            ]
        );

        // Add a breadcrumb location
        LoadLocation::create([
            'load_id' => $load->id,
            'driver_id' => $driver->id,
            'lat' => 40.7128,
            'lng' => -74.0060,
            'speed' => 55,
            'heading' => 90,
            'recorded_at' => now(),
        ]);
    }
}
