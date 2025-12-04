<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrailerResource\Pages;
use App\Models\Trailer;
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

class TrailerResource extends Resource
{
    protected static ?string $model = Trailer::class;
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static UnitEnum|string|null $navigationGroup = 'Assets';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Trailer details')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('trailer_number')->required(),
                        Forms\Components\TextInput::make('plate_number'),
                        Forms\Components\TextInput::make('VIN'),
                        Forms\Components\TextInput::make('type')->required(),
                        Forms\Components\TextInput::make('length'),
                        Forms\Components\TextInput::make('max_weight')->numeric(),
                        Forms\Components\KeyValue::make('reefer_settings')->label('Reefer settings (JSON)'),
                        Forms\Components\Select::make('status')->options([
                            'available' => 'Available',
                            'in_use' => 'In Use',
                            'maintenance' => 'Maintenance',
                            'inactive' => 'Inactive',
                        ])->default('available'),
                        Forms\Components\DatePicker::make('next_service_date'),
                        Forms\Components\TextInput::make('mileage')->numeric(),
                    ]),
                    Forms\Components\Textarea::make('notes'),
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
                Tables\Columns\TextColumn::make('trailer_number')->searchable()->sortable(),
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
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'available' => 'Available',
                    'in_use' => 'In Use',
                    'maintenance' => 'Maintenance',
                    'inactive' => 'Inactive',
                ]),
            ])
            ->emptyStateHeading('No trailers yet')
            ->emptyStateDescription('Add trailers to start managing availability and maintenance windows.')
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
            'index' => Pages\ListTrailers::route('/'),
            'create' => Pages\CreateTrailer::route('/create'),
            'edit' => Pages\EditTrailer::route('/{record}/edit'),
        ];
    }
}
