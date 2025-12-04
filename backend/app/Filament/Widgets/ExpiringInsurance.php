<?php

namespace App\Filament\Widgets;

use App\Models\Carrier;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ExpiringInsurance extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $threshold = Carbon::now()->addDays(30);

        return $table
            ->query(
                Carrier::query()
                    ->whereNotNull('insurance_expiry')
                    ->whereDate('insurance_expiry', '<=', $threshold)
                    ->orderBy('insurance_expiry')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Carrier')
                    ->searchable(),
                Tables\Columns\TextColumn::make('insurance_expiry')
                    ->label('Insurance Expiry')
                    ->date()
                    ->sortable()
                    ->color(fn ($state) => now()->diffInDays($state, false) <= 7 ? 'danger' : 'warning')
                    ->description(fn ($state) => Carbon::parse($state)->diffForHumans()),
                Tables\Columns\TextColumn::make('onboarding_status')
                    ->badge()
                    ->colors([
                        'gray' => 'new',
                        'warning' => 'pending_docs',
                        'success' => 'approved',
                        'danger' => 'blacklisted',
                    ]),
            ])
            ->paginated([10, 25])
            ->emptyStateHeading('No upcoming expirations')
            ->emptyStateDescription('All carrier insurance docs are valid beyond 30 days.');
    }
}
