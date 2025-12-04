<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use App\Filament\Resources\ClientResource\RelationManagers\CreditsRelationManager;
use App\Models\CreditBalance;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-building-office';
    protected static UnitEnum|string|null $navigationGroup = 'CRM & Accounts';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Client details')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')->required()->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->required()
                            ->options([
                                'shipper' => 'Shipper',
                                'broker' => 'Broker',
                                'direct_client' => 'Direct Client',
                            ]),
                        Forms\Components\TextInput::make('contact_person'),
                        Forms\Components\TextInput::make('phone'),
                        Forms\Components\TextInput::make('email')->email(),
                        Forms\Components\Select::make('status')
                            ->options(['active' => 'Active', 'inactive' => 'Inactive'])
                            ->default('active'),
                    ]),
                ]),
            Section::make('Billing & tax')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('payment_terms'),
                        Forms\Components\TextInput::make('credit_limit')->numeric(),
                        Forms\Components\Toggle::make('auto_apply_credit')->label('Auto-apply credits'),
                        Forms\Components\TextInput::make('credit_expiry_days')->numeric()->label('Credit expiry (days)')->helperText('Optional: expire credits after N days'),
                        Forms\Components\TextInput::make('tax_id'),
                    ]),
                ]),
            Section::make('Address')
                ->schema([
                    Grid::make(3)->schema([
                        Forms\Components\TextInput::make('billing_address')->columnSpan(2),
                        Forms\Components\TextInput::make('city'),
                        Forms\Components\TextInput::make('state'),
                        Forms\Components\TextInput::make('zip'),
                        Forms\Components\TextInput::make('country'),
                    ]),
                ]),
            Section::make('Notes & contract')
                ->schema([
                    Forms\Components\Textarea::make('notes')->columnSpanFull(),
                    FileUpload::make('contract_path')->label('Contract')->directory('client-contracts'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100])
            ->searchDebounce(500)
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('contact_person'),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('credits')
                    ->label('Credits')
                    ->state(fn (Client $record) => CreditBalance::where('entity_type', 'client')->where('entity_id', $record->id)->sum('remaining'))
                    ->formatStateUsing(fn ($state) => '$' . number_format($state ?? 0, 2))
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('created_at')->date(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options([
                    'shipper' => 'Shipper',
                    'broker' => 'Broker',
                    'direct_client' => 'Direct Client',
                ]),
                Tables\Filters\SelectFilter::make('status')->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ]),
            ])
            ->emptyStateHeading('No clients yet')
            ->emptyStateDescription('Add shippers/brokers to start invoicing and dispatch.')
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->emptyStateActions([
                Actions\CreateAction::make(),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\Action::make('add_credit')
                    ->label('Add credit')
                    ->icon('heroicon-o-arrow-up-on-square-stack')
                    ->form([
                        Forms\Components\TextInput::make('amount')->numeric()->required(),
                        Forms\Components\TextInput::make('reason'),
                        Forms\Components\DatePicker::make('expires_at')->label('Expires at'),
                    ])
                    ->action(function (array $data, Client $record) {
                        \App\Models\CreditBalance::create([
                            'entity_type' => 'client',
                            'entity_id' => $record->id,
                            'amount' => $data['amount'],
                            'remaining' => $data['amount'],
                            'reason' => $data['reason'] ?? null,
                            'expires_at' => $data['expires_at'] ?? null,
                            'source_type' => 'manual',
                        ]);
                    }),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            CreditsRelationManager::class,
        ];
    }
}
