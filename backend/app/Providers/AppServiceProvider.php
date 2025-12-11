<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('driver-location', function (Request $request) {
            $driverKey = $request->bearerToken() ?? $request->header('X-Driver-Token') ?? $request->ip();
            $limit = (int) config('tracking.rate_limits.driver_location', 60);
            return Limit::perMinute($limit)->by($driverKey);
        });

        RateLimiter::for('driver-jobs', function (Request $request) {
            $driverKey = $request->bearerToken() ?? $request->header('X-Driver-Token') ?? $request->ip();
            $limit = (int) config('tracking.rate_limits.driver_jobs', 30);
            return Limit::perMinute($limit)->by($driverKey);
        });

        RateLimiter::for('driver-status', function (Request $request) {
            $driverKey = $request->bearerToken() ?? $request->header('X-Driver-Token') ?? $request->ip();
            $limit = (int) config('tracking.rate_limits.driver_status', 30);
            return Limit::perMinute($limit)->by($driverKey);
        });
    }
}
