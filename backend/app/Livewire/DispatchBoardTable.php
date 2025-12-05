<?php

namespace App\Livewire;

use App\Models\Load;
use App\Models\User;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class DispatchBoardTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Load::query()
                    ->with(['client', 'carrier', 'driver', 'dispatcher', 'checkCalls'])
            )
            ->defaultSort('id', 'desc')
            ->paginationPageOptions([25, 50, 100])
            ->columns([
                TextColumn::make('load_number')
                    ->label('Load #')
                    ->url(fn (Load $record) => route('filament.admin.resources.loads.edit', $record))
                    ->openUrlInNewTab()
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'posted',
                        'info' => 'assigned',
                        'primary' => 'in_transit',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                    ])
                    ->label('Status')
                    ->sortable(),
                BadgeColumn::make('route_status')
                    ->label('SLA')
                    ->colors([
                        'danger' => 'late',
                        'warning' => 'at_risk',
                        'success' => 'on_time',
                    ])
                    ->formatStateUsing(fn (string $state) => str_replace('_', ' ', ucfirst($state)))
                    ->sortable(),
                TextColumn::make('dispatcher.name')
                    ->label('Dispatcher')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('driver.name')
                    ->label('Driver')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.name')
                    ->label('Client')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('carrier.name')
                    ->label('Carrier')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('profit')
                    ->label('Profit')
                    ->money('usd')
                    ->sortable(),
                TextColumn::make('margin')
                    ->label('Margin')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('checkCalls.reported_at')
                    ->label('Last event')
                    ->state(fn (Load $record) => optional($record->checkCalls()->latest('reported_at')->first())->reported_at)
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'posted' => 'Posted',
                        'assigned' => 'Assigned',
                        'in_transit' => 'In Transit',
                        'delivered' => 'Delivered',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('dispatcher_id')
                    ->label('Dispatcher')
                    ->options(fn () => User::query()->pluck('name', 'id'))
                    ->searchable(),
                Filter::make('unassigned')
                    ->label('Unassigned (carrier or driver)')
                    ->query(fn ($query) => $query->where(function ($q) {
                        $q->whereNull('carrier_id')->orWhereNull('driver_id');
                    })),
                Filter::make('late_only')
                    ->label('Late')
                    ->query(fn ($query) => $query
                        ->whereHas('stops', function ($q) {
                            $q->where('type', 'delivery')->whereDate('date_from', '<', now()->toDateString());
                        })
                        ->whereNotIn('status', ['delivered', 'completed'])),
                Filter::make('at_risk')
                    ->label('At risk (next 6h)')
                    ->query(fn ($query) => $query
                        ->whereHas('stops', function ($q) {
                            $q->where('type', 'delivery')
                                ->whereDate('date_from', '<=', now()->addHours(6)->toDateString());
                        })
                        ->whereNotIn('status', ['delivered', 'completed'])),
                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from'),
                        \Filament\Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->emptyStateHeading('No loads found')
            ->emptyStateDescription('Try adjusting filters or create a new load.');
    }

    public function render(): View
    {
        return view('livewire.dispatch-board-table');
    }
}
