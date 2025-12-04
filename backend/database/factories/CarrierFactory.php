<?php

namespace Database\Factories;

use App\Models\Carrier;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarrierFactory extends Factory
{
    protected $model = Carrier::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company . ' Logistics',
            'MC_number' => $this->faker->numerify('MC#####'),
            'DOT_number' => $this->faker->numerify('DOT#######'),
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->companyEmail,
            'dispatcher_contact' => $this->faker->name,
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'state' => $this->faker->stateAbbr,
            'zip' => $this->faker->postcode,
            'country' => 'USA',
            'insurance_company' => $this->faker->company,
            'insurance_policy_number' => $this->faker->bothify('POL#######'),
            'insurance_expiry' => $this->faker->dateTimeBetween('+1 month', '+1 year'),
            'payment_terms' => 'Quick pay',
            'auto_apply_credit' => false,
            'credit_expiry_days' => 30,
            'factoring_company' => $this->faker->company,
            'factoring_email' => $this->faker->companyEmail,
            'onboarding_status' => 'approved',
            'notes' => $this->faker->sentence,
        ];
    }
}
