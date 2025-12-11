<?php

namespace App\Jobs;

use App\Actions\Drivers\RecordLocationAction;
use App\Models\Driver;
use App\Models\Load;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PersistDriverLocationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $driverId;
    public int $loadId;
    public array $payload;

    public function __construct(int $driverId, int $loadId, array $payload)
    {
        $this->driverId = $driverId;
        $this->loadId = $loadId;
        $this->payload = $payload;
    }

    public function handle(RecordLocationAction $recordLocationAction): void
    {
        $driver = Driver::find($this->driverId);
        $load = Load::find($this->loadId);

        if (!$driver || !$load) {
            return;
        }

        try {
            $recordLocationAction->execute($driver, $load, $this->payload);
        } catch (\Throwable $e) {
            Log::error('Failed to persist driver location (queued)', [
                'driver_id' => $this->driverId,
                'load_id' => $this->loadId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
