<?php

namespace App\Filament\Widgets;

use App\Models\CarrierDocument;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DocumentStats extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        $now = Carbon::now();

        $new = CarrierDocument::where('created_at', '>=', $now->copy()->subDays(7))->count();
        $old = CarrierDocument::where('created_at', '<', $now->copy()->subDays(30))->count();
        $accepted = CarrierDocument::where('status', 'approved')->count();
        $removed = CarrierDocument::where('status', 'rejected')->count();
        $reviewed = CarrierDocument::where('status', '!=', 'pending')->count();

        return [
            Stat::make('New Documents (7d)', $new)->icon('heroicon-o-clock'),
            Stat::make('Old Documents (30d+)', $old)->icon('heroicon-o-archive-box'),
            Stat::make('Accepted Documents', $accepted)->icon('heroicon-o-check-circle'),
            Stat::make('Removed Documents', $removed)->icon('heroicon-o-x-circle')->color('danger'),
            Stat::make('Reviewed Documents', $reviewed)->icon('heroicon-o-eye')->color('warning'),
        ];
    }
}
