<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarrierResource\Pages;
use App\Models\Carrier;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\FileUpload;
use UnitEnum;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Models\CreditBalance;
use App\Filament\Resources\CarrierResource\RelationManagers\CreditsRelationManager;

class CarrierResource extends Resource
{
    protected static ?string $model = Carrier::class;
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-truck';
    protected static UnitEnum|string|null $navigationGroup = 'Operations';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Company')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\TextInput::make('legal_name')->label('Legal Name'),
                        Forms\Components\TextInput::make('dba_name')->label('DBA'),
                        Forms\Components\TextInput::make('dispatcher_contact'),
                        Forms\Components\TextInput::make('phone'),
                        Forms\Components\TextInput::make('email')->email(),
                        Forms\Components\TextInput::make('mc_number')->label('MC #'),
                        Forms\Components\TextInput::make('usd_ot_number')->label('USDOT #'),
                        Forms\Components\TextInput::make('DOT_number')->label('Legacy DOT #'),
                        Forms\Components\Select::make('onboarding_status')
                            ->options([
                                'new' => 'New',
                                'pending_docs' => 'Pending Docs',
                                'approved' => 'Approved',
                                'blacklisted' => 'Blacklisted',
                            ])
                            ->default('new'),
                    ]),
                ]),
            Section::make('Compliance & Safety')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('safety_rating'),
                        Forms\Components\TextInput::make('safer_profile_url')->url()->label('SAFER URL'),
                    ]),
                ]),
            Section::make('Address')
                ->schema([
                    Grid::make(3)->schema([
                        Forms\Components\TextInput::make('address')->columnSpan(2),
                        Forms\Components\TextInput::make('city'),
                        Forms\Components\TextInput::make('state'),
                        Forms\Components\TextInput::make('zip'),
                        Forms\Components\TextInput::make('country'),
                    ]),
                ]),
            Section::make('Insurance & Factoring')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('insurance_primary_name')->label('Insurer'),
                        Forms\Components\TextInput::make('insurance_policy_number')->label('Policy #'),
                        Forms\Components\DatePicker::make('insurance_expires_at')->label('Insurance expires'),
                        Forms\Components\Select::make('insurance_coverage_types')
                            ->multiple()
                            ->options([
                                'auto_liab' => 'Auto Liability',
                                'cargo' => 'Cargo',
                                'general_liab' => 'General Liability',
                                'workers_comp' => 'Workers Comp',
                                'other' => 'Other',
                            ]),
                        Forms\Components\KeyValue::make('insurance_limits')
                            ->label('Coverage limits')
                            ->keyLabel('Coverage')
                            ->valueLabel('Limit')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('payment_terms'),
                        Forms\Components\Toggle::make('auto_apply_credit')->label('Auto-apply credits'),
                        Forms\Components\TextInput::make('credit_expiry_days')->numeric()->label('Credit expiry (days)'),
                        Forms\Components\TextInput::make('factoring_company'),
                        Forms\Components\TextInput::make('factoring_email'),
                    ]),
                    Forms\Components\Textarea::make('notes')->columnSpanFull(),
                ]),
            Section::make('Documents')
                ->schema([
                    Grid::make(2)->schema([
                        FileUpload::make('w9_path')->label('W9')->directory('carrier-docs'),
                        FileUpload::make('coi_path')->label('COI')->directory('carrier-docs'),
                        FileUpload::make('coi_document_path')->label('COI (Compliance)')->directory('carrier-docs'),
                        FileUpload::make('carrier_packet_path')->label('Carrier Packet')->directory('carrier-docs'),
                        FileUpload::make('contract_path')->label('Contract')->directory('carrier-docs'),
                    ]),
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
                Tables\Columns\TextColumn::make('mc_number')->label('MC #'),
                Tables\Columns\TextColumn::make('usd_ot_number')->label('USDOT #'),
                Tables\Columns\TextColumn::make('insurance_expires_at')->date()->sortable(),
                Tables\Columns\TextColumn::make('onboarding_status')
                    ->badge()
                    ->colors([
                        'gray' => 'new',
                        'warning' => 'pending_docs',
                        'success' => 'approved',
                        'danger' => 'blacklisted',
                    ]),
                Tables\Columns\TextColumn::make('credits')
                    ->label('Credits')
                    ->state(fn (Carrier $record) => CreditBalance::where('entity_type', 'carrier')->where('entity_id', $record->id)->sum('remaining'))
                    ->formatStateUsing(fn ($state) => '$' . number_format($state ?? 0, 2))
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('city'),
                Tables\Columns\TextColumn::make('state'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('onboarding_status')->options([
                    'new' => 'New',
                    'pending_docs' => 'Pending Docs',
                    'approved' => 'Approved',
                    'blacklisted' => 'Blacklisted',
                ]),
                Tables\Filters\Filter::make('insurance_expiring')
                    ->label('Insurance expiring < 30d')
                    ->query(fn ($query) => $query
                        ->whereNotNull('insurance_expires_at')
                        ->whereDate('insurance_expires_at', '<=', now()->addDays(30))),
            ])
            ->emptyStateHeading('No carriers yet')
            ->emptyStateDescription('Add carriers to track onboarding status and docs.')
            ->headerActions([
                CreateAction::make(),
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                \Filament\Actions\Action::make('add_credit')
                    ->label('Add credit')
                    ->icon('heroicon-o-arrow-up-on-square-stack')
                    ->form([
                        Forms\Components\TextInput::make('amount')->numeric()->required(),
                        Forms\Components\TextInput::make('reason'),
                        Forms\Components\DatePicker::make('expires_at')->label('Expires at'),
                    ])
                    ->action(function (array $data, Carrier $record) {
                        CreditBalance::create([
                            'entity_type' => 'carrier',
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
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarriers::route('/'),
            'create' => Pages\CreateCarrier::route('/create'),
            'edit' => Pages\EditCarrier::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            CreditsRelationManager::class,
        ];
    }
}
