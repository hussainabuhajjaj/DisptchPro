<?php

namespace Database\Factories;

use App\Models\Carrier;
use App\Models\Driver;
use Illuminate\Database\Eloquent\Factories\Factory;

class DriverFactory extends Factory
{
    protected $model = Driver::class;

    public function definition(): array
    {
        return [
            'carrier_id' => Carrier::factory(),
            'name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'license_number' => $this->faker->bothify('LIC#######'),
            'license_state' => $this->faker->stateAbbr,
            'license_expiry' => $this->faker->dateTimeBetween('+6 months', '+3 years'),
            'CDL_type' => 'Class A',
            'endorsements' => ['T', 'N'],
            'address' => $this->faker->streetAddress,
            'emergency_contact' => $this->faker->name,
            'status' => 'active',
            'availability' => true,
            'notes' => $this->faker->sentence,
        ];
    }
}
