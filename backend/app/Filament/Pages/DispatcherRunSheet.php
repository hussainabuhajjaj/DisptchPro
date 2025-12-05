<?php

namespace App\Filament\Pages;

use App\Models\Driver;
use App\Models\Load;
use App\Models\LoadStop;
use App\Models\User;
use Filament\Pages\Page;
use UnitEnum;

class DispatcherRunSheet extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static UnitEnum|string|null $navigationGroup = 'Operations';
    protected static ?string $title = 'Run Sheet';
    protected static ?string $navigationLabel = 'Run Sheet';
    protected string $view = 'filament.pages.dispatcher-run-sheet';

    public function getStops()
    {
        $query = LoadStop::with(['loadRelation.client', 'loadRelation.carrier', 'loadRelation.driver', 'loadRelation.dispatcher'])
            ->whereNotNull('date_from');

        $date = request()->input('date');
        $from = request()->input('from');
        $to = request()->input('to');

        $rangeFrom = $from ?: ($date ?: now()->toDateString());
        $rangeTo = $to ?: $rangeFrom;

        $query->whereDate('date_from', '>=', $rangeFrom)
            ->whereDate('date_from', '<=', $rangeTo);

        if ($dispatcher = request()->input('dispatcher')) {
            $query->whereHas('loadRelation', fn ($q) => $q->where('dispatcher_id', $dispatcher));
        }

        if ($driver = request()->input('driver')) {
            $query->whereHas('loadRelation', fn ($q) => $q->where('driver_id', $driver));
        }

        if ($type = request()->input('type')) {
            $query->where('type', $type);
        }

        return $query
            ->orderBy('date_from')
            ->orderBy('sequence')
            ->paginate(50)
            ->withQueryString();
    }

    public function dispatcherOptions(): array
    {
        $dispatcherIds = Load::query()
            ->whereNotNull('dispatcher_id')
            ->whereHas('stops', fn ($q) => $q->whereNotNull('date_from'))
            ->distinct()
            ->pluck('dispatcher_id');

        return User::whereIn('id', $dispatcherIds)->pluck('name', 'id')->toArray();
    }

    public function driverOptions(): array
    {
        return Driver::query()->pluck('name', 'id')->toArray();
    }

    public function typeOptions(): array
    {
        return LoadStop::query()
            ->whereNotNull('type')
            ->distinct()
            ->pluck('type')
            ->sort()
            ->values()
            ->all();
    }
}
