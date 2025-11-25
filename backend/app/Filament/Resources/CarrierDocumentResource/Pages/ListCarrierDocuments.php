<?php

namespace App\Filament\Resources\CarrierDocumentResource\Pages;

use App\Filament\Resources\CarrierDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCarrierDocuments extends ListRecords
{
    protected static string $resource = CarrierDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
