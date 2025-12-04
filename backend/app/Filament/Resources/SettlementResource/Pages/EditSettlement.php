<?php

namespace App\Filament\Resources\SettlementResource\Pages;

use App\Filament\Resources\SettlementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\SettlementResource\Widgets\SettlementStats;
use App\Filament\Resources\SettlementResource\Widgets\SettlementCreditAlert;

class EditSettlement extends EditRecord
{
    protected static string $resource = SettlementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SettlementCreditAlert::class,
            SettlementStats::class,
        ];
    }
}
