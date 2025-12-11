<?php

namespace App\Providers;

use App\Models\Carrier;
use App\Models\CheckCall;
use App\Models\Document;
use App\Models\LoadLocation;
use App\Policies\CarrierPolicy;
use App\Policies\CheckCallPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\LoadLocationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        LoadLocation::class => LoadLocationPolicy::class,
        CheckCall::class => CheckCallPolicy::class,
        Document::class => DocumentPolicy::class,
        Carrier::class => CarrierPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
