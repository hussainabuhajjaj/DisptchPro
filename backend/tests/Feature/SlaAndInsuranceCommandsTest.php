<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Load;
use App\Models\LoadStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SlaAndInsuranceCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_insurance_expiry_notifies_once_per_day(): void
    {
        Notification::fake();
        Cache::flush();

        $carrier = Carrier::factory()->create([
            'insurance_expires_at' => Carbon::now()->addDays(5),
        ]);
        $user = User::factory()->create();

        $this->artisan('notify:carrier-insurance-expiry --days=7')->assertExitCode(0);
        Notification::assertSentTimes(\App\Notifications\CarrierInsuranceExpiryNotification::class, 1);

        // Second run same day should be memoized
        $this->artisan('notify:carrier-insurance-expiry --days=7')->assertExitCode(0);
        Notification::assertSentTimes(\App\Notifications\CarrierInsuranceExpiryNotification::class, 1);
    }

    public function test_sla_check_sends_alert_and_memoizes(): void
    {
        Notification::fake();
        Cache::flush();

        $user = User::factory()->create();

        $load = Load::factory()->create([
            'status' => 'assigned',
            'last_eta_minutes' => 120, // ETA two hours
        ]);

        LoadStop::factory()->create([
            'load_id' => $load->id,
            'sequence' => 1,
            'type' => 'delivery',
            'date_from' => Carbon::now()->addMinutes(60), // scheduled in 60 minutes
        ]);

        $this->artisan('sla:check --lookahead=180')->assertExitCode(0);
        Notification::assertSentTimes(\App\Notifications\SlaAlertNotification::class, 1);

        // Repeat should not send again due to cache key
        $this->artisan('sla:check --lookahead=180')->assertExitCode(0);
        Notification::assertSentTimes(\App\Notifications\SlaAlertNotification::class, 1);
    }
}
