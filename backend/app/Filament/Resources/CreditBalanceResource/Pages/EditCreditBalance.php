<?php

namespace App\Filament\Resources\CreditBalanceResource\Pages;

use App\Filament\Resources\CreditBalanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCreditBalance extends EditRecord
{
    protected static string $resource = CreditBalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
