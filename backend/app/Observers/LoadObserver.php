<?php

namespace App\Observers;

use App\Events\TmsMapUpdated;
use App\Models\Load;
use Illuminate\Support\Facades\Cache;

class LoadObserver
{
    public function saved(Load $load): void
    {
        Cache::forget('tms-map-data');
        broadcast(new TmsMapUpdated());
    }

    public function deleted(Load $load): void
    {
        Cache::forget('tms-map-data');
        broadcast(new TmsMapUpdated());
    }
}
