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

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';
    protected static ?string $title = 'Tasks';

    public function form(Schema $form): Schema
    {
        return $form->components([
            Section::make()
                ->schema([
                    TextInput::make('title')->required()->columnSpanFull(),
                    Textarea::make('notes')->rows(2)->columnSpanFull(),
                    Select::make('status')
                        ->options([
                            'open' => 'Open',
                            'in_progress' => 'In progress',
                            'done' => 'Done',
                        ])
                        ->default('open'),
                    Select::make('priority')
                        ->options([
                            'low' => 'Low',
                            'normal' => 'Normal',
                            'high' => 'High',
                        ])
                        ->default('normal'),
                    DateTimePicker::make('due_at')->label('Due'),
                    Select::make('assigned_to')
                        ->relationship('assignee', 'name')
                        ->label('Assignee')
                        ->searchable()
                        ->preload(),
                ])
                ->columns(2),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->wrap()->searchable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('priority')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('assignee.name')->label('Assignee')->toggleable(),
                Tables\Columns\TextColumn::make('due_at')->dateTime()->label('Due')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Created'),
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
