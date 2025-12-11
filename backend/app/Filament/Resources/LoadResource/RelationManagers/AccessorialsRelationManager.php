<?php

namespace App\Filament\Resources\LoadResource\RelationManagers;

use App\Models\Accessorial;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms;

class AccessorialsRelationManager extends RelationManager
{
    protected static string $relationship = 'accessorials';
    protected static ?string $recordTitleAttribute = 'type';

    public function form(Schema $form): Schema
    {
        return $form->schema([
            Forms\Components\Select::make('type')
                ->options([
                    'detention' => 'Detention',
                    'tonu' => 'TONU',
                    'lumper' => 'Lumper',
                    'layover' => 'Layover',
                    'other' => 'Other',
                ])
                ->required(),
            Forms\Components\TextInput::make('amount')->numeric()->required(),
            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ])
                ->default('pending'),
            Forms\Components\Textarea::make('note')->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('amount')->money('usd'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('approved_by')->label('Approved by'),
                Tables\Columns\TextColumn::make('approved_at')->since(),
                Tables\Columns\TextColumn::make('created_at')->since(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('approve')
                    ->requiresConfirmation()
                    ->visible(fn (Accessorial $record) => $record->status !== 'approved')
                    ->action(function (Accessorial $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
