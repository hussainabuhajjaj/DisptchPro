<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarrierDocumentResource\Pages;
use App\Models\CarrierDocument;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CarrierDocumentResource extends Resource
{
    protected static ?string $model = CarrierDocument::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-paper-clip';

    protected static \UnitEnum|string|null $navigationGroup = 'Carrier Onboarding';

    public static function getNavigationBadge(): ?string
    {
        $pending = CarrierDocument::where('status', 'pending')->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Document details')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\Select::make('draft_id')
                                ->relationship('draft', 'id')
                                ->required()
                                ->searchable(),
                            Forms\Components\Select::make('status')
                                ->options([
                                    'pending' => 'Pending',
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                ])
                                ->required(),
                            Forms\Components\TextInput::make('type')
                                ->datalist(['w9', 'coi', 'insurance', 'factoringNoa'])
                                ->required()
                                ->helperText('w9, coi, insurance, factoringNoa'),
                            Forms\Components\TextInput::make('file_name')
                                ->label('File name'),
                        ]),
                        Forms\Components\Textarea::make('reviewer_note')
                            ->label('Reviewer note')
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
                Tables\Columns\TextColumn::make('draft.id')->label('Draft')->sortable(),
                Tables\Columns\TextColumn::make('type')->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('file_name')->label('File'),
                Tables\Columns\TextColumn::make('reviewer_note')
                    ->label('Reviewer note')
                    ->limit(60)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'w9' => 'W-9',
                        'coi' => 'Certificate of Insurance',
                        'insurance' => 'Cargo/Insurance Binder',
                        'factoringNoa' => 'Factoring NOA',
                    ]),
                Tables\Filters\Filter::make('pending_only')
                    ->label('Pending only')
                    ->query(fn ($query) => $query->where('status', 'pending')),
                Tables\Filters\Filter::make('stale')
                    ->label('Stale (7d+)')
                    ->query(fn ($query) => $query->whereDate('updated_at', '<=', now()->subDays(7))),
            ])
            ->emptyStateHeading('No carrier documents yet')
            ->emptyStateDescription('Ingest W-9, COI, and other onboarding docs.')
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->emptyStateActions([
                Actions\CreateAction::make(),
            ])
            ->recordActions([
                Actions\Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (CarrierDocument $record) => $record->update([
                        'status' => 'approved',
                        'reviewer_note' => null,
                    ])),
                Actions\Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('reviewer_note')
                            ->label('Reason')
                            ->required()
                            ->maxLength(2000),
                    ])
                    ->action(fn (CarrierDocument $record, array $data) => $record->update([
                        'status' => 'rejected',
                        'reviewer_note' => $data['reviewer_note'],
                    ])),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ])
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarrierDocuments::route('/'),
            'create' => Pages\CreateCarrierDocument::route('/create'),
            'edit' => Pages\EditCarrierDocument::route('/{record}/edit'),
        ];
    }
}
