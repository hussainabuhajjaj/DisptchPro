<?php

namespace Database\Factories;

use App\Models\Truck;
use Illuminate\Database\Eloquent\Factories\Factory;

class TruckFactory extends Factory
{
    protected $model = Truck::class;

    public function definition(): array
    {
        return [
            'unit_number' => $this->faker->unique()->numerify('TRK###'),
            'plate_number' => $this->faker->unique()->bothify('PLT-####'),
            'VIN' => $this->faker->unique()->bothify('1FTSW21P#EA######'),
            'type' => 'Tractor',
            'make' => $this->faker->randomElement(['Freightliner', 'Volvo', 'Kenworth', 'Peterbilt']),
            'model' => $this->faker->numerify('Model ###'),
            'year' => $this->faker->year,
            'status' => 'available',
            'current_load_id' => null,
            'next_service_date' => $this->faker->dateTimeBetween('+1 month', '+6 months'),
            'mileage' => $this->faker->numberBetween(50000, 450000),
            'notes' => $this->faker->sentence,
        ];
    }
}
