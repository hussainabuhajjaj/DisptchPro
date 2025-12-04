<?php

namespace App\Filament\Resources\InvoiceResource\Widgets;

use App\Models\CreditBalance;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InvoiceStats extends StatsOverviewWidget
{
    public ?\App\Models\Invoice $record = null;

    protected function getCards(): array
    {
        if (!$this->record) {
            return [];
        }

        $credits = CreditBalance::where('entity_type', 'client')
            ->where('entity_id', $this->record->client_id)
            ->sum('remaining');

        $balance = $this->record->balance ?? $this->record->balance_due ?? ($this->record->total - $this->record->payments()->sum('amount'));
        $balance = max($balance, 0);

        return [
            Stat::make('Balance due', '$' . number_format($balance, 2)),
            Stat::make('Credits available', '$' . number_format($credits, 2))
                ->color($credits > 0 ? 'success' : 'gray'),
        ];
    }
}
