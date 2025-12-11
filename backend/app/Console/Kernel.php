<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('credits:auto-apply')->dailyAt('01:00');
        $schedule->command('loads:detention-calc')->hourly();
        $schedule->command('notify:carrier-insurance-expiry')->dailyAt('06:00');
        $schedule->command('sla:check')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}
