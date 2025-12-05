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
        $fallbackRoute = ['from_city' => 'Chicago', 'from_state' => 'IL', 'to_city' => 'Indianapolis', 'to_state' => 'IN'];
        $now = Carbon::now();

        $loads = Load::with('stops')->orderBy('id')->get();
        foreach ($loads as $index => $load) {
            if ($load->stops()->exists()) {
                continue;
            }
            $route = $fallbackRoute;

            LoadStop::create([
                'load_id' => $load->id,
                'sequence' => 1,
                'type' => 'pickup',
                'facility_name' => 'Warehouse',
                'city' => $route['from_city'],
                'state' => $route['from_state'],
                'country' => 'US',
                'date_from' => $now->copy()->addDays($index)->setTime(8, 0),
                'date_to' => $now->copy()->addDays($index)->setTime(10, 0),
            ]);

            LoadStop::create([
                'load_id' => $load->id,
                'sequence' => 2,
                'type' => 'delivery',
                'facility_name' => 'DC',
                'city' => $route['to_city'],
                'state' => $route['to_state'],
                'country' => 'US',
                'date_from' => $now->copy()->addDays($index + 1)->setTime(9, 0),
                'date_to' => $now->copy()->addDays($index + 1)->setTime(11, 0),
            ]);
        }
    }
}
