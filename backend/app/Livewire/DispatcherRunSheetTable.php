<?php

namespace App\Livewire;

use App\Models\LoadStop;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class DispatcherRunSheetTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LoadStop::query()
                    ->with(['loadRelation.client', 'loadRelation.carrier', 'loadRelation.driver', 'loadRelation.dispatcher'])
                    ->whereNotNull('date_from')
            )
            ->defaultSort('date_from', 'asc')
            ->paginationPageOptions([25, 50, 100])
            ->columns([
                TextColumn::make('date_from')
                    ->label('When')
                    ->dateTime('m/d H:i')
                    ->description(fn ($record) => $record->appointment_time ? 'Appt ' . $record->appointment_time : null)
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Stop')
                    ->formatStateUsing(fn ($state) => strtoupper($state ?? ''))
                    ->description(fn ($record) => trim(($record->city ?? '') . ', ' . ($record->state ?? ''))),
                TextColumn::make('loadRelation.load_number')
                    ->label('Load')
                    ->url(fn ($record) => $record->loadRelation ? route('filament.admin.resources.loads.edit', $record->loadRelation) : null, shouldOpenInNewTab: true)
                    ->placeholder('—')
                    ->description(fn ($record) => $record->loadRelation ? 'Status: ' . ucfirst(str_replace('_', ' ', $record->loadRelation->status)) : null)
                    ->sortable(),
                TextColumn::make('loadRelation.dispatcher.name')
                    ->label('Dispatcher')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('loadRelation.driver.name')
                    ->label('Driver')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('loadRelation.client.name')
                    ->label('Client / Carrier')
                    ->formatStateUsing(fn ($state, $record) => $record->loadRelation?->carrier?->name
                        ? ($record->loadRelation?->client?->name ?? 'Client') . ' · ' . $record->loadRelation?->carrier?->name
                        : ($record->loadRelation?->client?->name ?? 'Client'))
                    ->placeholder('—'),
                TextColumn::make('instructions')
                    ->label('Notes')
                    ->wrap()
                    ->limit(60)
                    ->placeholder('—'),
            ])
            ->filters([
                Filter::make('date_range')
                    ->label('Date range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('From'),
                        \Filament\Forms\Components\DatePicker::make('to')->label('To'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('date_from', '>=', $date))
                            ->when($data['to'] ?? null, fn ($q, $date) => $q->whereDate('date_from', '<=', $date));
                    }),
                SelectFilter::make('dispatcher')
                    ->label('Dispatcher')
                    ->options(fn () => LoadStop::query()
                        ->whereHas('loadRelation')
                        ->with('loadRelation.dispatcher')
                        ->get()
                        ->pluck('loadRelation.dispatcher.name', 'loadRelation.dispatcher_id')
                        ->filter()
                        ->unique()
                        ->toArray())
                    ->query(function ($query, $value) {
                        if (!$value) {
                            return $query;
                        }
                        return $query->whereHas('loadRelation', fn ($q) => $q->where('dispatcher_id', $value));
                    }),
                SelectFilter::make('driver')
                    ->label('Driver')
                    ->options(fn () => LoadStop::query()
                        ->whereHas('loadRelation')
                        ->with('loadRelation.driver')
                        ->get()
                        ->pluck('loadRelation.driver.name', 'loadRelation.driver_id')
                        ->filter()
                        ->unique()
                        ->toArray())
                    ->query(function ($query, $value) {
                        if (!$value) {
                            return $query;
                        }
                        return $query->whereHas('loadRelation', fn ($q) => $q->where('driver_id', $value));
                    }),
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(fn () => LoadStop::query()
                        ->select('type')
                        ->whereNotNull('type')
                        ->distinct()
                        ->pluck('type', 'type')
                        ->toArray()),
            ]);
    }

    public function render(): View
    {
        return view('livewire.dispatcher-run-sheet-table');
    }
}
