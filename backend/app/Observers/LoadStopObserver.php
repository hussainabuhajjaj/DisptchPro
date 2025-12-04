<?php

namespace App\Observers;

use App\Events\TmsMapUpdated;
use App\Models\LoadStop;
use Illuminate\Support\Facades\Cache;

class LoadStopObserver
{
    public function saved(LoadStop $stop): void
    {
        Cache::forget('tms-map-data');
        broadcast(new TmsMapUpdated());
    }

    public function deleted(LoadStop $stop): void
    {
        Cache::forget('tms-map-data');
        broadcast(new TmsMapUpdated());
    }
}
