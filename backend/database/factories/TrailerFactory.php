<?php

namespace Database\Factories;

use App\Models\Trailer;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrailerFactory extends Factory
{
    protected $model = Trailer::class;

    public function definition(): array
    {
        return [
            'trailer_number' => $this->faker->unique()->numerify('TRL###'),
            'plate_number' => $this->faker->unique()->bothify('TRL-####'),
            'VIN' => $this->faker->unique()->bothify('2GCEK19T#Y######'),
            'type' => $this->faker->randomElement(['Dry Van', 'Reefer', 'Flatbed']),
            'length' => 53,
            'max_weight' => 45000,
            'reefer_settings' => null,
            'status' => 'available',
            'next_service_date' => $this->faker->dateTimeBetween('+1 month', '+6 months'),
            'mileage' => $this->faker->numberBetween(10000, 300000),
            'notes' => $this->faker->sentence,
        ];
    }
}
