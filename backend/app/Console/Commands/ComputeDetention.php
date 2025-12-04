<?php

namespace App\Console\Commands;

use App\Models\Load;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ComputeDetention extends Command
{
    protected $signature = 'loads:detention-calc';
    protected $description = 'Compute detention/layover from check calls and add to accessorial_charges';

    public function handle(): int
    {
        $loads = Load::with(['checkCalls' => function ($q) {
            $q->orderBy('reported_at');
        }])->get();

        foreach ($loads as $load) {
            $pickupArrived = $load->checkCalls->firstWhere('status', 'arrived_pickup')?->reported_at;
            $pickupDeparted = $load->checkCalls->firstWhere('status', 'loaded')?->reported_at;
            $deliveryArrived = $load->checkCalls->firstWhere('status', 'arrived_delivery')?->reported_at;
            $deliveryDeparted = $load->checkCalls->firstWhere('status', 'unloaded')?->reported_at;

            $pickupDwell = $this->minutesBetween($pickupArrived, $pickupDeparted);
            $deliveryDwell = $this->minutesBetween($deliveryArrived, $deliveryDeparted);

            $accessorials = $load->accessorial_charges ?? [];
            $updated = false;

            if ($pickupDwell && $pickupDwell > 120) {
                $hours = round($pickupDwell / 60, 2);
                $accessorials['detention_pickup'] = [
                    'label' => 'Detention - Pickup',
                    'minutes' => $pickupDwell,
                    'hours' => $hours,
                    'revenue' => $hours * 50, // default $50/hr
                    'cost' => 0,
                ];
                $updated = true;
            }
            if ($deliveryDwell && $deliveryDwell > 120) {
                $hours = round($deliveryDwell / 60, 2);
                $accessorials['detention_delivery'] = [
                    'label' => 'Detention - Delivery',
                    'minutes' => $deliveryDwell,
                    'hours' => $hours,
                    'revenue' => $hours * 50, // default $50/hr
                    'cost' => 0,
                ];
                $updated = true;
            }

            if ($updated) {
                $load->accessorial_charges = $accessorials;
                $load->saveQuietly();
            }
        }

        $this->info('Detention calculation complete.');
        return self::SUCCESS;
    }

    protected function minutesBetween($from, $to): ?int
    {
        if (!$from || !$to) {
            return null;
        }
        return max($to->diffInMinutes($from), 0);
    }
}
