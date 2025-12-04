<?php

namespace App\Filament\Resources\SettlementResource\Widgets;

use App\Models\CreditBalance;
use App\Models\Settlement;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class SettlementCreditAlert extends Widget
{
    protected string $view = 'filament.resources.settlement-resource.widgets.credit-alert';

    public Settlement $record;

    protected static ?int $sort = 0;

    protected function getViewData(): array
    {
        $entityType = $this->record->settlement_type;
        $entityId = $this->record->entity_id;

        if (! $entityType || ! $entityId) {
            return [
                'expiringCount' => 0,
                'expiringTotal' => 0,
                'windowDays' => null,
            ];
        }

        $prefDays = optional($this->record->entity)->credit_expiry_days ?? 14;
        $cutoff = Carbon::now()->addDays($prefDays);

        $expiring = CreditBalance::query()
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where('remaining', '>', 0)
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<=', $cutoff)
            ->get();

        return [
            'expiringCount' => $expiring->count(),
            'expiringTotal' => $expiring->sum('remaining'),
            'windowDays' => $prefDays,
        ];
    }
}
