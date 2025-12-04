<?php

namespace Database\Seeders;

use App\Models\Carrier;
use Illuminate\Database\Seeder;

class CarrierSeeder extends Seeder
{
    public function run(): void
    {
        $carriers = [
            [
                'name' => 'Redline Transport',
                'MC_number' => 'MC123456',
                'DOT_number' => 'DOT654321',
                'phone' => '555-700-1111',
                'email' => 'dispatch@redline.com',
                'city' => 'Kansas City',
                'state' => 'MO',
                'onboarding_status' => 'approved',
                'insurance_company' => 'Allied Insurance',
                'insurance_policy_number' => 'POL-7890',
            ],
            [
                'name' => 'BlueSky Logistics',
                'MC_number' => 'MC987654',
                'DOT_number' => 'DOT123789',
                'phone' => '555-800-2222',
                'email' => 'ops@bluesky.com',
                'city' => 'Phoenix',
                'state' => 'AZ',
                'onboarding_status' => 'pending_docs',
                'insurance_company' => 'Shield Assurance',
                'insurance_policy_number' => 'POL-4567',
            ],
        ];

        foreach ($carriers as $data) {
            Carrier::updateOrCreate(
                ['MC_number' => $data['MC_number']],
                $data
            );
        }
    }
}
