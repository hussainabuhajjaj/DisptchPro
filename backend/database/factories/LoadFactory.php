<?php

namespace Database\Factories;

use App\Models\Load;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LoadFactory extends Factory
{
    protected $model = Load::class;

    public function definition(): array
    {
        return [
            'load_number' => 'LD-' . Str::upper(Str::random(6)),
            'status' => 'draft',
            'client_id' => Client::factory(),
            'carrier_id' => null,
            'driver_id' => null,
            'truck_id' => null,
            'trailer_id' => null,
            'dispatcher_id' => null,
            'trailer_type' => $this->faker->randomElement(['Dry Van', 'Reefer', 'Flatbed']),
            'rate_to_client' => $this->faker->randomFloat(2, 800, 4500),
            'rate_to_carrier' => $this->faker->randomFloat(2, 600, 3200),
            'fuel_surcharge' => $this->faker->randomFloat(2, 50, 300),
            'accessorial_charges' => [],
            'distance_miles' => $this->faker->numberBetween(200, 2200),
            'estimated_distance' => null,
            'commodity' => $this->faker->randomElement(['General freight', 'Food', 'Machinery']),
            'weight' => $this->faker->numberBetween(5000, 40000),
            'pieces' => $this->faker->numberBetween(1, 30),
            'equipment_requirements' => $this->faker->randomElement(['Straps', 'Pallet jack', 'Liftgate']),
            'reference_numbers' => ['ref' => Str::upper(Str::random(5))],
            'internal_notes' => $this->faker->sentence(),
            'driver_notes' => null,
            'route_polyline' => null,
            'route_distance_km' => null,
            'route_duration_hr' => null,
        ];
    }
}
