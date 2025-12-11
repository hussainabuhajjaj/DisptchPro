<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarrierOnboardingResource\Pages;
use App\Models\Carrier;
use App\Services\Compliance\SaferLookupService;
use App\Support\Auth\RoleGuard;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use UnitEnum;

class CarrierOnboardingResource extends Resource
{
    protected static ?string $model = Carrier::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static UnitEnum|string|null $navigationGroup = 'Compliance';

    public static function canViewAny(): bool
    {
        return RoleGuard::hasOpsAccess(auth()->user());
    }

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Identity')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('legal_name')->label('Legal Name')->required(),
                        Forms\Components\TextInput::make('dba_name')->label('DBA'),
                        Forms\Components\TextInput::make('usdot_number')->label('USDOT'),
                        Forms\Components\TextInput::make('mc_number')->label('MC'),
                        Forms\Components\TextInput::make('safer_profile_url')->label('SAFER Profile URL'),
                        Forms\Components\TextInput::make('safety_rating')->label('Safety Rating'),
                    ]),
                ]),
            Section::make('Insurance')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('insurance_primary_name')->label('Insurance Company'),
                        Forms\Components\TextInput::make('insurance_policy_number')->label('Policy #'),
                        Forms\Components\DateTimePicker::make('insurance_expires_at')->label('Insurance Expires'),
                        Forms\Components\DateTimePicker::make('coi_expires_at')->label('COI Expires'),
                        Forms\Components\TextInput::make('coi_document_path')->label('COI Path'),
                        Forms\Components\TextInput::make('insurance_limits')->label('Limits (JSON)')->helperText('Structured JSON for coverage.'),
                    ]),
                ]),
            Section::make('Onboarding')
                ->schema([
                    Forms\Components\KeyValue::make('onboarding_checklist')
                        ->keyLabel('Step')
                        ->valueLabel('Status')
                        ->helperText('e.g., W9=done, COI=missing, SafetyRecord=pending'),
                    Grid::make(2)->schema([
                        Forms\Components\Select::make('onboarding_verification_status')
                            ->options([
                                'pending' => 'Pending',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('pending'),
                        Forms\Components\DateTimePicker::make('onboarding_verified_at'),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('legal_name')->searchable(),
                Tables\Columns\TextColumn::make('mc_number')->label('MC')->sortable(),
                Tables\Columns\TextColumn::make('usdot_number')->label('USDOT')->sortable(),
                Tables\Columns\TextColumn::make('onboarding_verification_status')->badge(),
                Tables\Columns\TextColumn::make('coi_expires_at')->date()->label('COI Exp'),
                Tables\Columns\TextColumn::make('insurance_expires_at')->date()->label('Ins Exp'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('onboarding_verification_status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->recordActions([
                Action::make('fetchSafer')
                    ->label('Fetch SAFER')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function (Carrier $record) {
                        $service = app(SaferLookupService::class);
                        $resp = $service->lookup($record->usdot_number ?? $record->DOT_number, $record->mc_number ?? $record->MC_number);

                        if (($resp['status'] ?? '') !== 'ok') {
                            Notification::make()->danger()->title('SAFER lookup failed')->send();
                            return;
                        }

                        $data = $resp['data'] ?? [];
                        $record->forceFill([
                            'safer_snapshot' => $data,
                            'safety_rating' => $data['safety_rating'] ?? $record->safety_rating,
                            'safer_profile_url' => $record->safer_profile_url ?? 'https://safer.fmcsa.dot.gov',
                            'onboarding_verification_status' => $record->onboarding_verification_status ?? 'pending',
                        ])->save();

                        Notification::make()->success()->title('SAFER data refreshed')->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarrierOnboardings::route('/'),
            'create' => Pages\CreateCarrierOnboarding::route('/create'),
            'edit' => Pages\EditCarrierOnboarding::route('/{record}/edit'),
        ];
    }
}
