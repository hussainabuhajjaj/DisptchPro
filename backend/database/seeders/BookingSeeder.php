<?php

namespace Database\Seeders;

use App\Models\Booking;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $samples = [
            [
                'title' => 'Lead Magnet: Route Optimization Checklist - Alex Carter',
                'type' => 'demo',
                'status' => 'pending',
                'start_at' => $now->copy()->subDays(1),
                'carrier_name' => 'Alex Carter',
                'email' => 'alex@fleetco.com',
                'notes' => 'Role: Owner-Operator | Notes: TX → GA, reefer, avoid NYC',
            ],
            [
                'title' => 'Consultation with Jamie Logistics',
                'type' => 'onboarding',
                'status' => 'pending',
                'start_at' => $now->copy()->addDays(1)->setTime(10, 30),
                'carrier_name' => 'Jamie Logistics',
                'email' => 'ops@jamielogistics.com',
                'phone' => '+1 (404) 555-2388',
                'notes' => '2 reefers, prefers Southeast lanes, no NYC.',
            ],
            [
                'title' => 'Demo: Broker assist surge coverage',
                'type' => 'call',
                'status' => 'pending',
                'start_at' => $now->copy()->addDays(2)->setTime(14, 0),
                'carrier_name' => 'Beacon Brokerage',
                'email' => 'capacity@beaconbrokerage.com',
                'notes' => 'Wants surge coverage playbook + COI process.',
            ],
        ];

        foreach ($samples as $sample) {
            Booking::updateOrCreate(
                ['title' => $sample['title'], 'start_at' => $sample['start_at']],
                $sample
            );
        }
    }
}

