<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\InvoiceResource\Widgets\InvoiceStats;
use App\Filament\Resources\InvoiceResource\Widgets\InvoiceCreditAlert;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            InvoiceCreditAlert::class,
            InvoiceStats::class,
        ];
    }
}
