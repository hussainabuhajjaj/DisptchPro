<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class CreditsRelationManager extends RelationManager
{
    protected static string $relationship = 'credits';
    protected static ?string $title = 'Credits';

    public function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make()->schema([
                TextInput::make('amount')->numeric()->required(),
                TextInput::make('remaining')->numeric()->required(),
                TextInput::make('reason')->label('Reason'),
                DatePicker::make('expires_at')->label('Expires at'),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('amount')->money('usd')->label('Original'),
                Tables\Columns\TextColumn::make('remaining')
                    ->money('usd')
                    ->label('Remaining')
                    ->badge()
                    ->color(fn ($record) => ($record->expires_at && $record->expires_at->isPast()) ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('expires_at')
                    ->date()
                    ->badge()
                    ->color(fn ($record) => $record->expires_at && $record->expires_at->isPast() ? 'danger' : (($record->expires_at && $record->expires_at->isToday()) ? 'warning' : 'gray')),
                Tables\Columns\TextColumn::make('reason')->limit(30),
                Tables\Columns\TextColumn::make('source_type')->label('Source'),
                Tables\Columns\TextColumn::make('source_id')->label('Source ID'),
            ])
            ->headerActions([
                Action::make('add')
                    ->label('Add credit')
                    ->icon('heroicon-o-plus')
                    ->form($this->form(app(\Filament\Schemas\Schema::class))->getComponents())
                    ->action(function (array $data) {
                        $data['entity_type'] = 'client';
                        $data['remaining'] = $data['remaining'] ?? $data['amount'];
                        return $this->getOwnerRecord()->credits()->create($data);
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->paginated(false);
    }
}
