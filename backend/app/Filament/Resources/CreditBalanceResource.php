<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreditBalanceResource\Pages;
use App\Models\CreditBalance;
use App\Models\Client;
use App\Models\Carrier;
use App\Models\Driver;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\Summarizers\Sum;
use UnitEnum;
use BackedEnum;

class CreditBalanceResource extends Resource
{
    protected static ?string $model = CreditBalance::class;
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-credit-card';
    protected static UnitEnum|string|null $navigationGroup = 'Financials';
    protected static ?string $navigationLabel = 'Credits';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Credit')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('entity_type')
                            ->options([
                                'client' => 'Client',
                                'carrier' => 'Carrier',
                                'driver' => 'Driver',
                            ])
                            ->required()
                            ->live(),
                        Select::make('entity_id')
                            ->label('Entity')
                            ->options(function (callable $get) {
                                return match ($get('entity_type')) {
                                    'carrier' => Carrier::pluck('name', 'id'),
                                    'driver' => Driver::pluck('name', 'id'),
                                    default => Client::pluck('name', 'id'),
                                };
                            })
                            ->required()
                            ->searchable(),
                        TextInput::make('amount')->numeric()->required()->label('Original amount'),
                        TextInput::make('remaining')->numeric()->required()->label('Remaining'),
                        TextInput::make('source_type')->label('Source type'),
                        TextInput::make('source_id')->label('Source id')->numeric(),
                    ]),
                    Textarea::make('reason')->label('Reason'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('entity_type')->badge(),
                Tables\Columns\TextColumn::make('entity_id')->label('Entity ID'),
                Tables\Columns\TextColumn::make('source_type')->label('Source'),
                Tables\Columns\TextColumn::make('source_id'),
                Tables\Columns\TextColumn::make('expires_at')->date()->label('Expires'),
                Tables\Columns\TextColumn::make('amount')->money('usd')->summarize(Sum::make()->label('Total credits')),
                Tables\Columns\TextColumn::make('remaining')->money('usd')->summarize(Sum::make()->label('Remaining')),
                Tables\Columns\TextColumn::make('reason')->limit(30),
            ])
            ->filters([
                Tables\Filters\Filter::make('expiring_14')
                    ->label('Expiring <14d')
                    ->query(fn ($q) => $q->whereNotNull('expires_at')->whereDate('expires_at', '<=', now()->addDays(14))),
                Tables\Filters\Filter::make('expired')
                    ->label('Expired')
                    ->query(fn ($q) => $q->whereNotNull('expires_at')->whereDate('expires_at', '<', now())),
            ])
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCreditBalances::route('/'),
            'create' => Pages\CreateCreditBalance::route('/create'),
            'edit' => Pages\EditCreditBalance::route('/{record}/edit'),
        ];
    }
}
