<?php

namespace App\Console\Commands;

use App\Models\Carrier;
use App\Models\User;
use App\Notifications\CarrierInsuranceExpiryNotification;
use App\Support\Auth\RoleGuard;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class NotifyCarrierInsuranceExpiry extends Command
{
    protected $signature = 'notify:carrier-insurance-expiry {--days=30}';

    protected $description = 'Send notifications for carriers with insurance/COI expiring in the next N days (default 30).';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = Carbon::now()->addDays($days);

        $carriers = Carrier::query()
            ->where(function ($q) use ($cutoff) {
                $q->whereNotNull('insurance_expires_at')
                    ->whereDate('insurance_expires_at', '<=', $cutoff)
                    ->orWhere(function ($q2) use ($cutoff) {
                        $q2->whereNotNull('coi_expires_at')
                            ->whereDate('coi_expires_at', '<=', $cutoff);
                    });
            })
            ->get();

        if ($carriers->isEmpty()) {
            $this->info('No carriers expiring within ' . $days . ' days.');
            return self::SUCCESS;
        }

        $recipients = User::all()->filter(fn (User $u) => RoleGuard::hasOpsAccess($u));

        foreach ($carriers as $carrier) {
            $cacheKey = "carrier:expiry:{$carrier->id}:{$cutoff->toDateString()}";
            if (Cache::has($cacheKey)) {
                continue; // already notified for this window
            }
            foreach ($recipients as $user) {
                $user->notify(new CarrierInsuranceExpiryNotification($carrier));
            }
            Cache::put($cacheKey, true, now()->addDay());
        }

        $this->info('Sent ' . ($carriers->count() * max(1, $recipients->count())) . ' notifications.');

        return self::SUCCESS;
    }
}
