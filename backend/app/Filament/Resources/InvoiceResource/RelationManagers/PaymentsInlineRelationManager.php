<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;


class PaymentsInlineRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title = 'Payments';

    public function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make()->schema([
                Grid::make(2)->schema([
                    DatePicker::make('paid_at')->label('Paid at')->required(),
                    TextInput::make('amount')->numeric()->required(),
                    TextInput::make('method')->label('Method'),
                    TextInput::make('reference')->label('Reference'),
                ]),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('paid_at')->date(),
                Tables\Columns\TextColumn::make('amount')->money('usd'),
                Tables\Columns\TextColumn::make('method'),
                Tables\Columns\TextColumn::make('reference'),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(function () {
                        $this->getOwnerRecord()->refreshTotals();
                    }),
                DeleteAction::make()
                    ->after(function () {
                        $this->getOwnerRecord()->refreshTotals();
                    }),
            ])
            ->paginated(false);
    }
}
