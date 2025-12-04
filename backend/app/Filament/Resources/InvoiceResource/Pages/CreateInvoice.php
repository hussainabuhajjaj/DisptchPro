<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Schema;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (Schema::hasColumn('invoices', 'invoice_date')) {
            $data['invoice_date'] = $data['invoice_date'] ?? $data['issue_date'] ?? now();
        }

        return $data;
    }
}
