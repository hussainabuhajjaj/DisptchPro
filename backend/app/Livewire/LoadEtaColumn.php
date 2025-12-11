<?php

namespace App\Livewire;

use App\Models\Load;
use Filament\Tables\Columns\TextColumn;

class LoadEtaColumn
{
    public static function make(): TextColumn
    {
        return TextColumn::make('last_eta_minutes')
            ->label('ETA (min)')
            ->formatStateUsing(fn ($state) => $state ? $state . 'm' : '—')
            ->badge()
            ->colors([
                'danger' => fn ($record) => $record->route_status === 'late',
                'warning' => fn ($record) => $record->route_status === 'at_risk',
                'gray' => fn ($record) => !$record->last_eta_minutes,
            ])
            ->icon(fn ($record) => $record->route_status === 'late' ? 'heroicon-o-exclamation-triangle' : ($record->route_status === 'at_risk' ? 'heroicon-o-clock' : null));
    }
}
