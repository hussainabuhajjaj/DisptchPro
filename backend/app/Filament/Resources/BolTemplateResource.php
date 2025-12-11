<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BolTemplateResource\Pages;
use App\Models\BolTemplate;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\MarkdownEditor;
use UnitEnum;
use BackedEnum;

class BolTemplateResource extends Resource
{
    protected static ?string $model = BolTemplate::class;
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';
    protected static UnitEnum|string|null $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 90;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_default')
                    ->label('Default template'),
                MarkdownEditor::make('body')
                    ->label('Template body')
                    ->helperText('Use markdown / Blade placeholders to render BOL content.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\IconColumn::make('is_default')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->since()->label('Updated'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBolTemplates::route('/'),
            'create' => Pages\CreateBolTemplate::route('/create'),
            'edit' => Pages\EditBolTemplate::route('/{record}/edit'),
        ];
    }
}
