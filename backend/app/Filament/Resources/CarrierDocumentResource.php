<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarrierDocumentResource\Pages;
use App\Models\CarrierDocument;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CarrierDocumentResource extends Resource
{
    protected static ?string $model = CarrierDocument::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-paper-clip';

    protected static \UnitEnum|string|null $navigationGroup = 'Carrier Onboarding';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\Select::make('draft_id')
                    ->relationship('draft', 'id')
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('type')
                    ->datalist(['w9', 'coi', 'insurance', 'factoringNoa'])
                    ->required()
                    ->helperText('w9, coi, insurance, factoringNoa'),
                Forms\Components\TextInput::make('file_name')->label('File name'),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('reviewer_note')->label('Reviewer note'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
            ])
            ->actions([
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
            ->bulkActions([
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
