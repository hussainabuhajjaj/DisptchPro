<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Load;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class KpiStats extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today();
        $monthStart = $today->copy()->firstOfMonth();

        $activeLoads = Load::whereIn('status', ['posted', 'assigned', 'in_transit'])->count();
        $pickupsToday = Load::whereDate('created_at', $today)->count(); // replace with stop pickup date when available
        $deliveriesToday = Load::whereDate('updated_at', $today)->where('status', 'delivered')->count();

        $invoices = Invoice::whereBetween('invoice_date', [$monthStart, $today])->get();
        $revenue = $invoices->sum('total');
        $profit = Load::whereBetween('created_at', [$monthStart, $today])->get()->sum->profit;

        return [
            Stat::make('Active loads', $activeLoads)->color('success'),
            Stat::make('Today pickups', $pickupsToday)->color('info'),
            Stat::make('Today deliveries', $deliveriesToday)->color('info'),
            Stat::make('MTD revenue', '$' . number_format($revenue, 2))->color('primary'),
            Stat::make('MTD profit', '$' . number_format($profit, 2))->color('primary'),
        ];
    }
}
