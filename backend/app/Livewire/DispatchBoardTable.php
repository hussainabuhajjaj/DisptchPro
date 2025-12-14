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
use Filament\Tables\Columns\IconColumn;
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
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistColumnSearchesInSession()
            ->persistSortInSession()
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
                    ->icon(fn (string $state) => $state === 'late' ? 'heroicon-o-exclamation-triangle' : ($state === 'at_risk' ? 'heroicon-o-clock' : 'heroicon-o-check-circle'))
                    ->sortable(),
                IconColumn::make('conflict')
                    ->label('Conflict')
                    ->boolean()
                    ->state(fn (Load $record) => $this->hasDriverConflict($record))
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->tooltip(fn (Load $record) => $this->hasDriverConflict($record) ? 'Driver has overlapping active load' : 'No conflict'),
                IconColumn::make('no_recent_check_call')
                    ->label('Check-call')
                    ->boolean()
                    ->state(fn (Load $record) => $this->noRecentCheckCall($record))
                    ->trueIcon('heroicon-o-exclamation-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->tooltip(fn (Load $record) => $this->noRecentCheckCall($record) ? 'No check-call in 12h' : 'Recent check-call'),
                \App\Livewire\LoadEtaColumn::make(),
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
                    ->description(fn (Load $record) => $this->lastCheckCallAgo($record))
                    ->sortable(),
            ])
            ->filters([
                Filter::make('no_recent_check_call')
                    ->label('No check-call in 12h')
                    ->query(fn ($query) => $query->whereDoesntHave('checkCalls', function ($q) {
                        $q->where('reported_at', '>=', now()->subHours(12));
                    })),
                Filter::make('unassigned')
                    ->label('Unassigned (carrier or driver)')
                    ->query(fn ($query) => $query->where(function ($q) {
                        $q->whereNull('carrier_id')->orWhereNull('driver_id');
                    })),
                Filter::make('conflicts_only')
                    ->label('Driver conflicts')
                    ->query(fn ($query) => $query->whereHas('driver', function ($driverQuery) {
                        $driverQuery->whereHas('loads', function ($q) {
                            $q->whereNotIn('status', ['delivered', 'completed', 'cancelled']);
                        });
                    })),
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

    protected function lastCheckCallAgo(Load $load): ?string
    {
        $last = optional($load->checkCalls()->latest('reported_at')->first())->reported_at;
        if (!$last) {
            return 'No check-calls';
        }
        return $last->diffForHumans();
    }

    protected function hasDriverConflict(Load $load): bool
    {
        $driverId = $load->driver_id;
        if (!$driverId) {
            return false;
        }

        return Load::query()
            ->where('driver_id', $driverId)
            ->whereNotIn('status', ['delivered', 'completed', 'cancelled'])
            ->where('id', '<>', $load->id)
            ->exists();
    }

    protected function noRecentCheckCall(Load $load): bool
    {
        $last = optional($load->checkCalls()->latest('reported_at')->first())->reported_at;
        if (!$last) {
            return true;
        }
        return $last->lt(now()->subHours(12));
    }

    public function render(): View
    {
        return view('livewire.dispatch-board-table');
    }
}
