<?php

namespace App\Filament\Resources\LoadResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PodsRelationManager extends RelationManager
{
    protected static string $relationship = 'pods';
    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $form): Schema
    {
        return $form->schema([
            Forms\Components\TextInput::make('signer_name'),
            Forms\Components\TextInput::make('signer_title'),
            Forms\Components\DateTimePicker::make('signed_at'),
            Forms\Components\TextInput::make('photo_path')->label('Photo path')->disabled(),
            Forms\Components\KeyValue::make('location'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('signer_name'),
                Tables\Columns\TextColumn::make('signed_at')->since(),
                Tables\Columns\TextColumn::make('photo_path')
                    ->label('Photo')
                    ->url(fn ($record) => $record->photo_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($record->photo_path) : null, true)
                    ->openUrlInNewTab(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
