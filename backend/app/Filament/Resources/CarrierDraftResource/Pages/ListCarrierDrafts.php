<?php

namespace App\Filament\Resources\CarrierDraftResource\Pages;

use App\Filament\Resources\CarrierDraftResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCarrierDrafts extends ListRecords
{
    protected static string $resource = CarrierDraftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
