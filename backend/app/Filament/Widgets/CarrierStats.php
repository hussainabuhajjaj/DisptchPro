<?php

namespace App\Filament\Widgets;

use App\Models\CarrierProfile;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CarrierStats extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        $now = Carbon::now();

        $new = CarrierProfile::where('created_at', '>=', $now->copy()->subDays(7))->count();
        $old = CarrierProfile::where('created_at', '<', $now->copy()->subDays(30))->count();
        $accepted = CarrierProfile::where('status', 'approved')->count();
        $removed = CarrierProfile::where('status', 'rejected')->count();
        $reviewed = CarrierProfile::whereIn('status', ['submitted'])->count();

        return [
            Stat::make('New Carriers (7d)', $new)->icon('heroicon-o-clock'),
            Stat::make('Old Carriers (30d+)', $old)->icon('heroicon-o-archive-box'),
            Stat::make('Accepted Carriers', $accepted)->icon('heroicon-o-check-circle'),
            Stat::make('Removed Carriers', $removed)->icon('heroicon-o-x-circle')->color('danger'),
            Stat::make('Reviewed Carriers', $reviewed)->icon('heroicon-o-eye')->color('warning'),
        ];
    }
}
