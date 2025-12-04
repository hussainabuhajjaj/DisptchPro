<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarrierDraftResource\Pages;
use App\Models\CarrierDraft;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CarrierDraftResource extends Resource
{
    protected static ?string $model = CarrierDraft::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected static \UnitEnum|string|null $navigationGroup = 'Carrier Onboarding';

    public static function getNavigationBadge(): ?string
    {
        $submitted = CarrierDraft::where('status', 'submitted')->count();
        return $submitted > 0 ? (string) $submitted : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Draft')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\Select::make('user_id')
                                ->relationship('user', 'email')
                                ->searchable()
                                ->required(),
                            Forms\Components\Select::make('status')
                                ->options([
                                    'draft' => 'Draft',
                                    'submitted' => 'Submitted',
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                ])
                                ->required(),
                        ]),
                        Forms\Components\KeyValue::make('data')
                            ->label('Payload')
                            ->columnSpanFull()
                            ->helperText('JSON payload captured from the onboarding wizard.'),
                        Forms\Components\KeyValue::make('consent')
                            ->label('Consent')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->paginated([25, 50, 100])
            ->searchDebounce(500)
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'submitted',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\Filter::make('stale')
                    ->label('Stale (14d+)')
                    ->query(fn ($query) => $query->whereDate('updated_at', '<=', now()->subDays(14))),
            ])
            ->emptyStateHeading('No drafts yet')
            ->emptyStateDescription('Onboard carriers with drafts and approvals.')
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->emptyStateActions([
                Actions\CreateAction::make(),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarrierDrafts::route('/'),
            'create' => Pages\CreateCarrierDraft::route('/create'),
            'edit' => Pages\EditCarrierDraft::route('/{record}/edit'),
            'view' => Pages\ViewCarrierDraft::route('/{record}'),
        ];
    }
}
