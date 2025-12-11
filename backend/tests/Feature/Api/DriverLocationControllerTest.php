<?php

namespace Tests\Feature\Api;

use App\Models\Driver;
use App\Models\DriverApiToken;
use App\Models\Load;
use App\Models\LoadStop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DriverLocationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_authentication(): void
    {
        $response = $this->postJson('/api/driver/location', []);
        $response->assertStatus(401);
    }

    public function test_records_location_with_bearer_token(): void
    {
        Config::set('tracking.queue_locations', false);

        $driver = Driver::factory()->create();
        $load = Load::factory()->create([
            'driver_id' => $driver->id,
            'status' => 'assigned',
            'lifecycle_status' => 'assigned',
        ]);
        LoadStop::factory()->create([
            'load_id' => $load->id,
            'sequence' => 1,
            'type' => 'delivery',
            'lat' => 40.0,
            'lng' => -90.0,
        ]);

        $token = DriverApiToken::issueForDriver($driver, 'test', now()->addDay());

        $payload = [
            'load_id' => $load->id,
            'lat' => 39.9,
            'lng' => -89.9,
            'speed' => 60,
            'heading' => 90,
            'accuracy_m' => 15,
        ];

        $response = $this->postJson('/api/driver/location', $payload, [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'eta_minutes']);
        $this->assertDatabaseHas('load_locations', [
            'load_id' => $load->id,
            'driver_id' => $driver->id,
        ]);
    }

    public function test_rejects_unrealistic_jump(): void
    {
        Config::set('tracking.queue_locations', false);

        $driver = Driver::factory()->create();
        $load = Load::factory()->create([
            'driver_id' => $driver->id,
            'last_lat' => 40.0,
            'last_lng' => -90.0,
            'last_location_at' => now(),
        ]);
        $token = DriverApiToken::issueForDriver($driver, 'test', now()->addDay());

        $payload = [
            'load_id' => $load->id,
            'lat' => 10.0,
            'lng' => -120.0,
            'speed' => 10,
            'accuracy_m' => 10,
        ];

        $response = $this->postJson('/api/driver/location', $payload, [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(202);
        $response->assertJson(['ignored' => true]);
    }
}
