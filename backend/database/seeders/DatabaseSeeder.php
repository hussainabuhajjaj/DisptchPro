<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\LandingSectionSeeder;
use Database\Seeders\MediaSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\BookingSeeder;
use Database\Seeders\TestimonialSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ClientSeeder;
use Database\Seeders\CarrierSeeder;
use Database\Seeders\DriverSeeder;
use Database\Seeders\LoadSeeder;
use Database\Seeders\LoadStopSeeder;
use Database\Seeders\LoadStressSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            LandingSectionSeeder::class,
            MediaSeeder::class,
            TestimonialSeeder::class,
            BookingSeeder::class,
            RoleSeeder::class,
            ClientSeeder::class,
            CarrierSeeder::class,
            DriverSeeder::class,
            LoadSeeder::class,
            LoadStopSeeder::class,
            ScenarioSeeder::class,
            LoadStressSeeder::class,
        ]);
    }
}
