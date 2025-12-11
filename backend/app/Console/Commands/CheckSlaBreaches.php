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

        $loads = Load::query()
            ->whereNotIn('status', ['delivered', 'completed', 'cancelled'])
            ->whereNotNull('last_eta_minutes')
            ->with(['stops' => fn ($q) => $q->orderBy('sequence')])
            ->get();

        $recipients = User::all()->filter(fn (User $u) => RoleGuard::hasOpsAccess($u));

        $count = 0;

        foreach ($loads as $load) {
            $final = $load->stops->where('type', 'delivery')->sortBy('sequence')->last();
            if (!$final || !$final->date_from) {
                continue;
            }

            $etaArrival = $now->copy()->addMinutes((int) $load->last_eta_minutes);
            $scheduled = $final->date_from instanceof Carbon ? $final->date_from : Carbon::parse($final->date_from);

            // Only alert if ETA beyond scheduled and within lookahead horizon
            if ($etaArrival->lte($scheduled) || $etaArrival->gt($scheduled->copy()->addMinutes($lookahead))) {
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
}
