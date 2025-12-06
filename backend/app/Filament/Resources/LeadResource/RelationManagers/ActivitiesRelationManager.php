<?php

namespace App\Filament\Resources\LeadResource\RelationManagers;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';
    protected static ?string $title = 'Activities';

    public function form(Schema $form): Schema
    {
        return $form->components([
            Section::make()
                ->schema([
                    Select::make('type')
                        ->options([
                            'call' => 'Call',
                            'sms' => 'SMS',
                            'email' => 'Email',
                            'note' => 'Note',
                            'demo' => 'Demo',
                            'quote' => 'Quote',
                        ])
                        ->native(false),
                    DateTimePicker::make('happened_at')->label('When')->default(now()),
                    Select::make('user_id')
                        ->label('User')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->native(false),
                    Textarea::make('summary')->rows(3)->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('summary')->wrap()->limit(80),
                Tables\Columns\TextColumn::make('user.name')->label('User'),
                Tables\Columns\TextColumn::make('happened_at')->dateTime()->label('When')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Logged'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->recordActions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
