<?php

namespace Tests\Feature;

use App\Jobs\SendLoadAlerts;
use App\Models\Load;
use App\Models\LoadStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SlackNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_load_alerts_posts_to_slack_webhook(): void
    {
        // Arrange: configure a fake Slack webhook
        Config::set('services.slack.webhook_url', 'https://hooks.slack.test/alert');
        Http::fake([
            'hooks.slack.test/*' => Http::response(['ok' => true], 200),
        ]);

        // Dispatcher user
        $dispatcher = User::factory()->create();

        // Create a load with a delivery stop in the past so it is "late"
        $load = Load::factory()->create([
            'dispatcher_id' => $dispatcher->id,
            'status' => 'in_transit',
        ]);

        LoadStop::factory()->create([
            'load_id' => $load->id,
            'sequence' => 1,
            'type' => 'delivery',
            'date_from' => Carbon::now()->subDay(),
        ]);

        // Act
        (new SendLoadAlerts())->handle();

        // Assert Slack webhook was called
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'hooks.slack.test/alert')
                && str_contains($request['text'] ?? '', 'Load');
        });
    }
}
