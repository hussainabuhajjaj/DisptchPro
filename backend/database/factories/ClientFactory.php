<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'type' => 'shipper',
            'contact_person' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'billing_address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'state' => $this->faker->stateAbbr,
            'zip' => $this->faker->postcode,
            'country' => 'USA',
            'payment_terms' => 'Net 30',
            'credit_limit' => $this->faker->numberBetween(5000, 25000),
            'auto_apply_credit' => false,
            'credit_expiry_days' => 30,
            'tax_id' => $this->faker->regexify('[0-9]{2}-[0-9]{7}'),
            'notes' => $this->faker->sentence,
            'status' => 'active',
        ];
    }
}
