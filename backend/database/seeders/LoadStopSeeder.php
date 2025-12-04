<?php

namespace Database\Seeders;

use App\Models\Load;
use App\Models\LoadStop;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LoadStopSeeder extends Seeder
{
    public function run(): void
    {
        $routes = [
            ['from_city' => 'Dallas', 'from_state' => 'TX', 'to_city' => 'Atlanta', 'to_state' => 'GA'],
            ['from_city' => 'Phoenix', 'from_state' => 'AZ', 'to_city' => 'Kansas City', 'to_state' => 'MO'],
            ['from_city' => 'Chicago', 'from_state' => 'IL', 'to_city' => 'Seattle', 'to_state' => 'WA'],
        ];

        $loads = Load::orderBy('id')->get();
        $now = Carbon::now();

        foreach ($loads as $index => $load) {
            $route = $routes[$index % count($routes)];

            // Pickups
            LoadStop::updateOrCreate(
                [
                    'load_id' => $load->id,
                    'sequence' => 1,
                    'type' => 'pickup',
                ],
                [
                    'facility_name' => 'Warehouse',
                    'city' => $route['from_city'],
                    'state' => $route['from_state'],
                    'date_from' => $now->copy()->addDays($index)->toDateString(),
                ]
            );

            // Deliveries
            LoadStop::updateOrCreate(
                [
                    'load_id' => $load->id,
                    'sequence' => 2,
                    'type' => 'delivery',
                ],
                [
                    'facility_name' => 'DC',
                    'city' => $route['to_city'],
                    'state' => $route['to_state'],
                    'date_from' => $now->copy()->addDays($index + 2)->toDateString(),
                ]
            );
        }
    }
}
