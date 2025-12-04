<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\Driver;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        $carrier = Carrier::first();

        $drivers = [
            [
                'carrier_id' => $carrier?->id,
                'name' => 'Hector Ramirez',
                'phone' => '555-900-3333',
                'email' => 'hector.ramirez@example.com',
                'license_number' => 'TX-1234567',
                'license_state' => 'TX',
                'status' => 'active',
                'availability' => true,
            ],
            [
                'carrier_id' => $carrier?->id,
                'name' => 'Dana Lee',
                'phone' => '555-900-4444',
                'email' => 'dana.lee@example.com',
                'license_number' => 'AZ-9876543',
                'license_state' => 'AZ',
                'status' => 'active',
                'availability' => true,
            ],
        ];

        foreach ($drivers as $data) {
            Driver::updateOrCreate(
                ['email' => $data['email']],
                $data
            );
        }
    }
}
