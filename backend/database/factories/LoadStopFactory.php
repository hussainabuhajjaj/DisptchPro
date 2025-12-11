<?php

namespace Database\Factories;

use App\Models\Load;
use App\Models\LoadStop;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoadStopFactory extends Factory
{
    protected $model = LoadStop::class;

    public function definition(): array
    {
        return [
            'load_id' => Load::factory(),
            'sequence' => 1,
            'type' => 'delivery',
            'facility_name' => 'Test Stop',
            'address' => '123 Main St',
            'city' => 'Chicago',
            'state' => 'IL',
            'zip' => '60601',
            'country' => 'US',
            'lat' => 41.8781,
            'lng' => -87.6298,
            'date_from' => now()->addDay(),
            'date_to' => now()->addDay()->addHours(2),
            'timezone' => 'America/Chicago',
            'window_start' => now()->addDay(),
            'window_end' => now()->addDay()->addHours(2),
            'geofence_radius_m' => 500,
            'is_appointment_required' => false,
        ];
    }
}
