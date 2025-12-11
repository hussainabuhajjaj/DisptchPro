<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use App\Support\Auth\RoleGuard;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue as ComponentsKeyValue;
use Filament\Forms\Components\TextInput;
use Filament\KeyValue;
use UnitEnum;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected static UnitEnum|string|null $navigationGroup = 'Operations';

    public static function canViewAny(): bool
    {
        return RoleGuard::hasOpsAccess(auth()->user());
    }

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Document')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('documentable_type')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('documentable_id')
                            ->numeric()
                            ->required(),
                        TextInput::make('type')->required(),
                        TextInput::make('category'),
                        TextInput::make('file_path')->required(),
                        TextInput::make('original_name'),
                        TextInput::make('mime_type'),
                        TextInput::make('size')->numeric(),
                        TextInput::make('uploaded_by')->numeric(),
                        DateTimePicker::make('uploaded_at'),
                         ComponentsKeyValue::make('metadata')->columnSpanFull(),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns(components: [
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('documentable_type')->label('Type')->searchable(),
                Tables\Columns\TextColumn::make('documentable_id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('type')->sortable()->badge(),
                Tables\Columns\TextColumn::make('category')->sortable(),
                Tables\Columns\TextColumn::make('uploaded_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('file_path')->wrap(),
            ])->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'coi' => 'COI',
                        'rate_con' => 'Rate Confirmation',
                        'bol' => 'BOL',
                        'pod' => 'POD',
                    ]),
                ])
                ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])->defaultSort('uploaded_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}
