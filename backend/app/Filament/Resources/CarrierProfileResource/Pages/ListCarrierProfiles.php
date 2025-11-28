<?php

namespace App\Filament\Resources\CarrierProfileResource\Pages;

use App\Filament\Resources\CarrierProfileResource;
use Filament\Resources\Pages\ListRecords;

class ListCarrierProfiles extends ListRecords
{
    protected static string $resource = CarrierProfileResource::class;

    protected function getHeaderActions(): array
    {
        // Read-only listing for review.
        return [];
    }
}
