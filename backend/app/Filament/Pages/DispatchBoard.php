<?php

namespace App\Filament\Pages;

use App\Models\Load;
use App\Models\User;
use Filament\Pages\Page;
use UnitEnum;

class DispatchBoard extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-group';
    protected static UnitEnum|string|null $navigationGroup = 'Operations';
    protected string $view = 'filament.pages.dispatch-board';
    protected static ?string $title = 'Dispatch Board';

    protected function getViewData(): array
    {
        return [
            'dispatchers' => $this->getDispatchers(),
        ];
    }

    protected function getDispatchers()
    {
        $dispatcherIds = Load::query()
            ->whereNotNull('dispatcher_id')
            ->distinct()
            ->pluck('dispatcher_id');

        return User::query()
            ->whereIn('id', $dispatcherIds)
            ->pluck('name', 'id');
    }


    public function getLoadsByStatus(): array
    {
        $statuses = ['posted', 'assigned', 'in_transit', 'delivered', 'completed'];
        $unassignedOnly = request()->boolean('unassigned');
        $dispatcherId = request()->input('dispatcher');
        $lateOnly = request()->boolean('late');
        $atRisk = request()->boolean('at_risk');

        return collect($statuses)->mapWithKeys(function ($status) use ($unassignedOnly, $dispatcherId, $lateOnly, $atRisk) {
            $loads = Load::with(['client', 'carrier', 'driver'])
                ->where('status', $status)
                ->when($unassignedOnly, fn ($q) => $q->whereNull('carrier_id')->orWhereNull('driver_id'))
                ->when($dispatcherId, fn ($q) => $q->where('dispatcher_id', $dispatcherId))
                ->when($lateOnly, function ($q) {
                    $q->whereNotIn('status', ['delivered', 'completed'])
                        ->whereHas('stops', fn ($sq) => $sq
                            ->where('type', 'delivery')
                            ->whereDate('date_from', '<', now()->toDateString()));
                })
                ->when($atRisk, function ($q) {
                    $q->whereNotIn('status', ['delivered', 'completed'])
                        ->whereHas('stops', fn ($sq) => $sq
                            ->where('type', 'delivery')
                            ->whereDate('date_from', '<=', now()->addHours(6)->toDateString()));
                })
                ->with('stops', 'checkCalls')
                ->orderByDesc('id')
                ->take(20)
                ->get();
            return [$status => $loads];
        })->all();
    }

    public function slaFlags(Load $load): array
    {
        $flags = [];
        $deliveryStop = $load->stops?->firstWhere('type', 'delivery');
        if ($deliveryStop && $deliveryStop->date_from && now()->gt($deliveryStop->date_from) && !in_array($load->status, ['delivered', 'completed'])) {
            $flags[] = 'Late delivery window';
        }
        $accessorials = $load->accessorial_charges ?? [];
        if (($accessorials['detention_pickup']['minutes'] ?? 0) > 0) {
            $flags[] = 'Pickup detention';
        }
        if (($accessorials['detention_delivery']['minutes'] ?? 0) > 0) {
            $flags[] = 'Delivery detention';
        }
        return $flags;
    }
}
