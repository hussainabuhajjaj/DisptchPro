<?php

namespace App\Providers;

use App\Models\Load;
use App\Observers\LoadObserver;
use App\Models\LoadStop;
use App\Observers\LoadStopObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [];

    public function boot(): void
    {
        Load::observe(LoadObserver::class);
        LoadStop::observe(LoadStopObserver::class);
    }
}
