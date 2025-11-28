<?php

namespace App\Filament\Resources\CarrierProfileResource\Pages;

use App\Filament\Resources\CarrierProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCarrierProfile extends ViewRecord
{
    protected static string $resource = CarrierProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
