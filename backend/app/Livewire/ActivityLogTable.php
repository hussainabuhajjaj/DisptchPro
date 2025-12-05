<?php

namespace App\Livewire;

use App\Models\User;
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
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class ActivityLogTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Activity::query()->with('causer'))
            ->columns([
                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
                TextColumn::make('causer.name')
                    ->label('Causer')
                    ->placeholder('System')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Action')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('subject')
                    ->label('Subject')
                    ->state(fn (Activity $record) => $record->subject_type ? class_basename($record->subject_type) . ' #' . $record->subject_id : '—')
                    ->searchable(['subject_type', 'subject_id'])
                    ->sortable(),
                TextColumn::make('properties')
                    ->label('Changes')
                    ->state(function (Activity $record) {
                        $data = $record->properties?->toArray();
                        if (empty($data)) {
                            return null;
                        }
                        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                    })
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginationPageOptions([25, 50, 100])
            ->filters([
                SelectFilter::make('causer_id')
                    ->label('Causer')
                    ->options($this->causerOptions()),
                Filter::make('action')
                    ->label('Action contains')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('value')
                            ->placeholder('created, updated, deleted'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['value'] ?? null, fn ($q, $value) => $q->where('description', 'like', '%' . $value . '%'));
                    }),
                Filter::make('subject')
                    ->label('Subject')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('value')
                            ->placeholder('Load, User, 42'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['value'] ?? null, function ($q, $value) {
                            $q->where(function ($sq) use ($value) {
                                $sq->where('subject_id', $value)
                                    ->orWhere('subject_type', 'like', '%' . $value . '%');
                            });
                        });
                    }),
                Filter::make('created_at')
                    ->label('Date range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('From'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('To'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->filtersFormColumns(3)
            ->emptyStateHeading('No activity found')
            ->emptyStateDescription('Try adjusting filters or date range.');
    }

    public function render()
    {
        return view('livewire.activity-log-table');
    }

    protected function causerOptions(): array
    {
        $ids = Activity::query()
            ->whereNotNull('causer_id')
            ->distinct()
            ->pluck('causer_id');

        return User::whereIn('id', $ids)->pluck('name', 'id')->toArray();
    }
}
