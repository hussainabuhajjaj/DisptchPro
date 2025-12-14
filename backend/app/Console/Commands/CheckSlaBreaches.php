<?php

namespace App\Console\Commands;

use App\Models\Load;
use App\Models\User;
use App\Notifications\SlaAlertNotification;
use App\Support\Auth\RoleGuard;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class CheckSlaBreaches extends Command
{
    protected $signature = 'sla:check {--lookahead=120}';

    protected $description = 'Scan loads for late ETA vs delivery window and send SLA alerts.';

    public function handle(): int
    {
        $lookahead = (int) $this->option('lookahead');
        $now = Carbon::now();
        $checkCallThresholdHours = (int) config('sla.check_call_hours', 12);

        $loads = Load::query()
            ->whereNotIn('status', ['delivered', 'completed', 'cancelled'])
            ->with([
                'stops' => fn ($q) => $q->orderBy('sequence'),
                'checkCalls' => fn ($q) => $q->latest('reported_at')->limit(1),
            ])
            ->get();

        $recipients = User::all()->filter(fn (User $u) => RoleGuard::hasOpsAccess($u));

        $count = 0;

        foreach ($loads as $load) {
            $final = $load->stops->where('type', 'delivery')->sortBy('sequence')->last();
            if (!$final || !$final->date_from) {
                continue;
            }

            $lastCall = $load->checkCalls->first();
            if (!$lastCall || $lastCall->reported_at->lt($now->copy()->subHours($checkCallThresholdHours))) {
                $count += $this->notifyOnce($load, 'No check-call in last ' . $checkCallThresholdHours . ' hours', 'no-check-call', $recipients, 6);
            }

            // Route status fallback (late/at risk even without ETA projection)
            if ($load->route_status === 'late') {
                $count += $this->notifyOnce($load, 'Delivery window missed (route status late)', 'route-late', $recipients);
            } elseif ($load->route_status === 'at_risk') {
                $count += $this->notifyOnce($load, 'Delivery window near; at risk', 'route-at-risk', $recipients, 3);
            }

            $etaArrival = $now->copy()->addMinutes((int) $load->last_eta_minutes);
            $scheduled = $final->date_from instanceof Carbon ? $final->date_from : Carbon::parse($final->date_from);

            // Only alert if ETA beyond scheduled and within lookahead horizon
            if (!$load->last_eta_minutes || $etaArrival->lte($scheduled) || $etaArrival->gt($scheduled->copy()->addMinutes($lookahead))) {
                continue;
            }

            $cacheKey = "sla:load:{$load->id}:{$scheduled->toDateString()}";
            if (Cache::has($cacheKey)) {
                continue;
            }

            foreach ($recipients as $user) {
                $user->notify(new SlaAlertNotification(
                    $load->load_number ?? '#',
                    'ETA projected to miss delivery window',
                    $load->status,
                    $load->id
                ));
            }

            Cache::put($cacheKey, true, now()->addDay());
            $count++;
        }

        $this->info("SLA alerts sent for {$count} loads.");

        return self::SUCCESS;
    }

    protected function notifyOnce(Load $load, string $reason, string $suffix, $recipients, int $hoursTtl = 6): int
    {
        $cacheKey = "sla:load:{$load->id}:{$suffix}";
        if (Cache::has($cacheKey)) {
            return 0;
        }

        foreach ($recipients as $user) {
            $user->notify(new SlaAlertNotification(
                $load->load_number ?? '#',
                $reason,
                $load->status,
                $load->id
            ));
        }

        Cache::put($cacheKey, true, now()->addHours($hoursTtl));
        return 1;
    }
}
