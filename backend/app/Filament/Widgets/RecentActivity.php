<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Spatie\Activitylog\Models\Activity;

class RecentActivity extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Activity::query()
                    ->with('causer')
                    ->latest()
                    ->limit(50)
            )
            ->paginated([10, 25])
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->label('Action')
                    ->wrap(),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('User')
                    ->placeholder('System')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('When')
                    ->since()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Subject')
                    ->formatStateUsing(fn ($state, $record) => class_basename($state) . ' #' . $record->subject_id)
                    ->toggleable(),
            ])
            ->emptyStateHeading('No activity yet')
            ->emptyStateDescription('Actions and changes will appear here.');
    }
}
