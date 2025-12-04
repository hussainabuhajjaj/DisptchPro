<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TruckResource\Pages;
use App\Models\Truck;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class TruckResource extends Resource
{
    protected static ?string $model = Truck::class;
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-8-tooth';
    protected static UnitEnum|string|null $navigationGroup = 'Assets';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Truck details')
                ->schema([
                   Grid::make(2)->schema([
                        Forms\Components\TextInput::make('unit_number')->required(),
                        Forms\Components\TextInput::make('plate_number'),
                        Forms\Components\TextInput::make('VIN'),
                        Forms\Components\TextInput::make('type'),
                        Forms\Components\TextInput::make('make'),
                        Forms\Components\TextInput::make('model'),
                        Forms\Components\TextInput::make('year')->numeric(),
                        Forms\Components\Select::make('status')->options([
                            'available' => 'Available',
                            'in_use' => 'In Use',
                            'maintenance' => 'Maintenance',
                            'inactive' => 'Inactive',
                        ])->default('available'),
                        Forms\Components\DatePicker::make('next_service_date'),
                        Forms\Components\TextInput::make('mileage')->numeric(),
                    ]),
                    Forms\Components\Textarea::make('notes')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100])
            ->searchDebounce(500)
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('unit_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'available',
                        'warning' => 'maintenance',
                        'info' => 'in_use',
                        'gray' => 'inactive',
                    ]),
                Tables\Columns\TextColumn::make('next_service_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('mileage'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'available' => 'Available',
                    'in_use' => 'In Use',
                    'maintenance' => 'Maintenance',
                    'inactive' => 'Inactive',
                ]),
            ])
            ->emptyStateHeading('No trucks yet')
            ->emptyStateDescription('Add your first truck to start tracking maintenance and assignments.')
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->emptyStateActions([
                Actions\CreateAction::make(),
            ])
            ->recordActions([
                Actions\EditAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrucks::route('/'),
            'create' => Pages\CreateTruck::route('/create'),
            'edit' => Pages\EditTruck::route('/{record}/edit'),
        ];
    }
}
