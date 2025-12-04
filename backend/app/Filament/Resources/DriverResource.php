<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverResource\Pages;
use App\Models\Driver;
use App\Models\Carrier;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-user';
    protected static UnitEnum|string|null $navigationGroup = 'Operations';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Driver')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\Select::make('carrier_id')
                            ->label('Carrier')
                            ->options(Carrier::query()->pluck('name', 'id'))
                            ->searchable(),
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\TextInput::make('phone'),
                        Forms\Components\TextInput::make('email')->email(),
                        Forms\Components\TextInput::make('address')->columnSpan(2),
                        Forms\Components\Select::make('status')->options([
                            'active' => 'Active',
                            'inactive' => 'Inactive',
                            'suspended' => 'Suspended',
                        ])->default('active'),
                        Forms\Components\Toggle::make('availability')->default(true),
                    ]),
                ]),
            Section::make('Licensing')
                ->schema([
                    Grid::make(3)->schema([
                        Forms\Components\TextInput::make('license_number'),
                        Forms\Components\TextInput::make('license_state'),
                        Forms\Components\DatePicker::make('license_expiry'),
                    ]),
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('CDL_type'),
                        Forms\Components\TagsInput::make('endorsements'),
                    ]),
                ]),
            Section::make('Emergency & notes')
                ->schema([
                    Forms\Components\TextInput::make('emergency_contact'),
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
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('carrier.name')->label('Carrier')->sortable(),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('license_expiry')->date(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\IconColumn::make('availability')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'suspended' => 'Suspended',
                ]),
                Tables\Filters\TernaryFilter::make('availability'),
            ])
            ->emptyStateHeading('No drivers yet')
            ->emptyStateDescription('Add drivers and link them to carriers and loads.')
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
            'index' => Pages\ListDrivers::route('/'),
            'create' => Pages\CreateDriver::route('/create'),
            'edit' => Pages\EditDriver::route('/{record}/edit'),
        ];
    }
}
