<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadTagResource\Pages;
use App\Models\LeadTag;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class LeadTagResource extends Resource
{
    protected static ?string $model = LeadTag::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-tag';
    protected static UnitEnum|string|null $navigationGroup = 'CRM';
    protected static ?string $navigationLabel = 'Lead Tags';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    TextInput::make('name')->required()->maxLength(100),
                    TextInput::make('color')->label('Hex color')->placeholder('#2563eb'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                ColorColumn::make('color')->label('Color'),
                TextColumn::make('created_at')->date()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
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
            'index' => Pages\ListLeadTags::route('/'),
            'create' => Pages\CreateLeadTag::route('/create'),
            'edit' => Pages\EditLeadTag::route('/{record}/edit'),
        ];
    }
}
