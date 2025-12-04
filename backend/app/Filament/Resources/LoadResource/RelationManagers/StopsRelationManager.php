<?php

namespace App\Filament\Resources\LoadResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class StopsRelationManager extends RelationManager
{
    protected static string $relationship = 'stops';

    public function form(Schema $form): Schema
    {
        return $form->schema([
            Forms\Components\TextInput::make('sequence')->numeric()->required(),
            Forms\Components\Select::make('type')
                ->options([
                    'pickup' => 'Pickup',
                    'delivery' => 'Delivery',
                    'fuel' => 'Fuel',
                    'break' => 'Break',
                    'rest' => 'Rest',
                    'inspection' => 'Inspection',
                    'service' => 'Service',
                ])
                ->required(),
            Forms\Components\TextInput::make('facility_name'),
            Forms\Components\TextInput::make('address'),
            Forms\Components\TextInput::make('city'),
            Forms\Components\TextInput::make('state'),
            Forms\Components\TextInput::make('zip'),
            Forms\Components\TextInput::make('country'),
            Forms\Components\DateTimePicker::make('date_from'),
            Forms\Components\DateTimePicker::make('date_to'),
            Forms\Components\TextInput::make('appointment_time'),
            Forms\Components\TextInput::make('contact_person'),
            Forms\Components\TextInput::make('contact_phone'),
            Forms\Components\Textarea::make('instructions'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sequence')->sortable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('facility_name'),
                Tables\Columns\TextColumn::make('city'),
                Tables\Columns\TextColumn::make('date_from')->dateTime(),
                Tables\Columns\TextColumn::make('date_to')->dateTime(),
            ])
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
