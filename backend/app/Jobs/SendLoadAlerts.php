<?php

namespace App\Jobs;

use App\Models\Load;
use App\Notifications\LoadAlertNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Http;

class SendLoadAlerts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $loads = Load::with(['dispatcher', 'stops'])
            ->whereNotIn('status', ['delivered', 'completed', 'cancelled'])
            ->orderByDesc('id')
            ->get();

        $now = now()->toDateString();
        $alerts = [];

        foreach ($loads as $load) {
            // Prefer explicit end_date if present, otherwise use latest delivery stop date.
            $endDate = $load->end_date ?? optional($load->stops->where('type', 'delivery')->max('date_from'))->toDateString();
            if (!$endDate) {
                continue;
            }
            if ($endDate < $now) {
                $alerts[] = ['load' => $load, 'status' => 'late', 'message' => "Load {$load->load_number} is late (end: {$endDate})"];
            } elseif ($endDate <= now()->addHours(6)->toDateString()) {
                $alerts[] = ['load' => $load, 'status' => 'at_risk', 'message' => "Load {$load->load_number} at risk (end: {$endDate})"];
            }
        }

        foreach ($alerts as $alert) {
            $load = $alert['load'];
            $dispatcher = $load->dispatcher;
            if ($dispatcher) {
                $dispatcher->notify(new LoadAlertNotification($alert['message'], [
                    'load_id' => $load->id,
                    'load_number' => $load->load_number,
                    'status' => $alert['status'],
                    'url' => route('filament.admin.resources.loads.edit', $load),
                ]));
            }

            // Optional Slack webhook (config services.slack.webhook_url or SLA_SLACK_WEBHOOK fallback)
            $webhook = config('services.slack.webhook_url') ?? env('SLA_SLACK_WEBHOOK');
            if ($webhook) {
                Http::post($webhook, [
                    'text' => $alert['message'],
                ]);
            }
        }
    }
}
