<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarrierProfileResource\Pages;
use App\Models\CarrierProfile;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CarrierProfileResource extends Resource
{
    protected static ?string $model = CarrierProfile::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static \UnitEnum|string|null $navigationGroup = 'Carrier Onboarding';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Carrier Info')->schema([
                    Forms\Components\TextInput::make('carrier_info.companyName')->label('Company')->disabled(),
                    Forms\Components\TextInput::make('carrier_info.mainContact')->label('Main Contact')->disabled(),
                    Forms\Components\TextInput::make('carrier_info.email')->label('Email')->disabled(),
                    Forms\Components\TextInput::make('carrier_info.officePhone')->label('Office Phone')->disabled(),
                    Forms\Components\TextInput::make('carrier_info.cellPhone')->label('Cell Phone')->disabled(),
                    Forms\Components\KeyValue::make('carrier_info')
                        ->label('Full Carrier Info')
                        ->disableAddingRows()
                        ->disableDeletingRows()
                        ->disabled()
                        ->columnSpanFull(),
                ]),
                Forms\Components\Section::make('Equipment')->schema([
                    Forms\Components\KeyValue::make('equipment_info')
                        ->label('Equipment Info')
                        ->disableAddingRows()
                        ->disableDeletingRows()
                        ->disabled(),
                ])->collapsed(),
                Forms\Components\Section::make('Operations')->schema([
                    Forms\Components\KeyValue::make('operation_info')
                        ->label('Operation Info')
                        ->disableAddingRows()
                        ->disableDeletingRows()
                        ->disabled(),
                ])->collapsed(),
                Forms\Components\Section::make('Factoring')->schema([
                    Forms\Components\KeyValue::make('factoring_info')
                        ->label('Factoring Info')
                        ->disableAddingRows()
                        ->disableDeletingRows()
                        ->disabled(),
                ])->collapsed(),
                Forms\Components\Section::make('Insurance')->schema([
                    Forms\Components\KeyValue::make('insurance_info')
                        ->label('Insurance Info')
                        ->disableAddingRows()
                        ->disableDeletingRows()
                        ->disabled(),
                ])->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('carrier_info.companyName')
                    ->label('Company')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'submitted',
                        'warning' => 'pending',
                        'gray' => 'draft',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => static::getUrl('view', ['record' => $record])),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarrierProfiles::route('/'),
            'view' => Pages\ViewCarrierProfile::route('/{record}'),
        ];
    }
}
