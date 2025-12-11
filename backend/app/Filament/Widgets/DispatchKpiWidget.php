<?php

namespace App\Filament\Widgets;

use App\Models\Load;
use App\Models\Driver;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DispatchKpiWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getCards(): array
    {
        $late = Load::query()
            ->whereHas('stops', function ($q) {
                $q->where('type', 'delivery')->whereDate('date_from', '<', now()->toDateString());
            })
            ->whereNotIn('status', ['delivered', 'completed', 'cancelled'])
            ->count();

        $atRisk = Load::query()
            ->whereHas('stops', function ($q) {
                $q->where('type', 'delivery')
                    ->whereDate('date_from', '<=', now()->addHours(6)->toDateString());
            })
            ->whereNotIn('status', ['delivered', 'completed', 'cancelled'])
            ->count();

        $noRecentCheck = Load::query()
            ->whereDoesntHave('checkCalls', function ($q) {
                $q->where('reported_at', '>=', now()->subHours(12));
            })
            ->count();

        $conflicts = Load::query()
            ->whereNotIn('status', ['delivered', 'completed', 'cancelled'])
            ->whereHas('driver', function ($driverQuery) {
                $driverQuery->whereHas('loads', function ($q) {
                    $q->whereNotIn('status', ['delivered', 'completed', 'cancelled']);
                });
            })
            ->count();

        return [
            Stat::make('Late', $late)->color($late > 0 ? 'danger' : 'success')->icon('heroicon-o-exclamation-triangle'),
            Stat::make('At risk (6h)', $atRisk)->color($atRisk > 0 ? 'warning' : 'success')->icon('heroicon-o-clock'),
            Stat::make('No check-call 12h', $noRecentCheck)->color($noRecentCheck > 0 ? 'warning' : 'success')->icon('heroicon-o-exclamation-circle'),
            Stat::make('Driver conflicts', $conflicts)->color($conflicts > 0 ? 'danger' : 'success')->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}
