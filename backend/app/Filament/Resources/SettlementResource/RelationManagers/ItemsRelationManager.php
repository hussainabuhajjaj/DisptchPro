<?php

namespace App\Filament\Resources\SettlementResource\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema as FormSchema;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Line items';

    public function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make()
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('description')->required()->columnSpan(2),
                        TextInput::make('quantity')->numeric()->default(1)->required(),
                        TextInput::make('rate')->numeric()->default(0)->required(),
                        TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->helperText('Auto-calculated as qty × rate'),
                    ]),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('description')->wrap(),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('rate')->money('usd'),
                Tables\Columns\TextColumn::make('amount')->money('usd'),
            ])
            ->headerActions([
                Action::make('add')
                    ->label('Add')
                    ->icon('heroicon-o-plus')
                    ->form($this->form(app(FormSchema::class))->getComponents())
                    ->action(function (array $data) {
                        $data['amount'] = ($data['quantity'] ?? 1) * ($data['rate'] ?? 0);
                        $record = $this->getOwnerRecord()->items()->create($data);
                        $this->getOwnerRecord()->refreshTotals();
                        return $record;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->using(function ($record, array $data) {
                        $data['amount'] = ($data['quantity'] ?? 1) * ($data['rate'] ?? 0);
                        $record->update($data);
                        $this->getOwnerRecord()->refreshTotals();
                        return $record;
                    }),
                DeleteAction::make()
                    ->after(function () {
                        $this->getOwnerRecord()->refreshTotals();
                    }),
            ])
            ->paginated(false);
    }
}
