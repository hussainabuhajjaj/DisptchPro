<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoadLocationResource\Pages;
use App\Models\LoadLocation;
use App\Support\Auth\RoleGuard;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use UnitEnum;

class LoadLocationResource extends Resource
{
    protected static ?string $model = LoadLocation::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-map-pin';

    protected static UnitEnum|string|null $navigationGroup = 'Operations';

    public static function canViewAny(): bool
    {
        return RoleGuard::hasOpsAccess(auth()->user());
    }

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Location')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\Select::make('load_id')
                            ->label('Load')
                            ->relationship('loadRelation', 'load_number')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('driver_id')
                            ->label('Driver')
                            ->relationship('driver', 'name')
                            ->searchable(),
                        Forms\Components\TextInput::make('lat')->numeric()->required(),
                        Forms\Components\TextInput::make('lng')->numeric()->required(),
                        Forms\Components\TextInput::make('speed')->numeric()->suffix('mph'),
                        Forms\Components\TextInput::make('heading')->numeric(),
                        Forms\Components\TextInput::make('accuracy_m')->numeric()->suffix('m'),
                        Forms\Components\TextInput::make('source')->maxLength(50),
                        Forms\Components\TextInput::make('track_id')->maxLength(64),
                        Forms\Components\DateTimePicker::make('recorded_at')->required(),
                        Forms\Components\Toggle::make('is_valid')->default(true),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('loadRelation.load_number')->label('Load')->searchable(),
                Tables\Columns\TextColumn::make('driver.name')->label('Driver')->searchable(),
                Tables\Columns\TextColumn::make('lat')->sortable(),
                Tables\Columns\TextColumn::make('lng')->sortable(),
                Tables\Columns\TextColumn::make('speed')->label('Speed')->sortable(),
                Tables\Columns\TextColumn::make('heading')->label('Heading'),
                Tables\Columns\TextColumn::make('recorded_at')->dateTime()->sortable(),
                Tables\Columns\IconColumn::make('is_valid')->boolean(),
            ])
            ->filters([
                Tables\Filters\Filter::make('recent')
                    ->label('Last 24h')
                    ->query(fn ($q) => $q->where('recorded_at', '>=', now()->subDay())),
            ])
            
            ->recordActions([
              EditAction::make(),
               DeleteAction::make(),
            ])
            ->defaultSort('recorded_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoadLocations::route('/'),
            'create' => Pages\CreateLoadLocation::route('/create'),
            'edit' => Pages\EditLoadLocation::route('/{record}/edit'),
        ];
    }
}
