<?php

namespace App\Filament\Resources\InvoiceResource\Widgets;

use App\Models\CreditBalance;
use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class InvoiceCreditAlert extends Widget
{
    protected string $view = 'filament.resources.invoice-resource.widgets.credit-alert';

    public Invoice $record;

    protected static ?int $sort = 0;

    protected function getViewData(): array
    {
        $client = $this->record->client;

        if (! $client) {
            return [
                'expiringCount' => 0,
                'expiringTotal' => 0,
                'windowDays' => null,
            ];
        }

        $windowDays = $client->credit_expiry_days ?? 14;
        $cutoff = Carbon::now()->addDays($windowDays);

        $expiring = CreditBalance::query()
            ->where('entity_type', 'client')
            ->where('entity_id', $client->id)
            ->where('remaining', '>', 0)
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<=', $cutoff)
            ->get();

        return [
            'expiringCount' => $expiring->count(),
            'expiringTotal' => $expiring->sum('remaining'),
            'windowDays' => $windowDays,
        ];
    }
}
