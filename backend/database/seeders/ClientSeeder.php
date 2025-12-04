<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'name' => 'Acme Foods',
                'type' => 'shipper',
                'contact_person' => 'Laura Chen',
                'phone' => '555-101-2020',
                'email' => 'logistics@acmefoods.com',
                'city' => 'Dallas',
                'state' => 'TX',
                'status' => 'active',
                'payment_terms' => 'Net 30',
            ],
            [
                'name' => 'Metro Brokers',
                'type' => 'broker',
                'contact_person' => 'Sam Patel',
                'phone' => '555-555-1212',
                'email' => 'ops@metrobrokers.com',
                'city' => 'Chicago',
                'state' => 'IL',
                'status' => 'active',
                'payment_terms' => 'Net 45',
            ],
            [
                'name' => 'Northline Manufacturing',
                'type' => 'direct_client',
                'contact_person' => 'Kim Alvarez',
                'phone' => '555-303-4040',
                'email' => 'shipping@northline.com',
                'city' => 'Atlanta',
                'state' => 'GA',
                'status' => 'active',
                'payment_terms' => 'Net 30',
            ],
        ];

        foreach ($clients as $data) {
            Client::updateOrCreate(
                ['email' => $data['email'] ?? null, 'name' => $data['name']],
                $data
            );
        }
    }
}
