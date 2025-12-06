<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadSourceResource\Pages;
use App\Models\LeadSource;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class LeadSourceResource extends Resource
{
    protected static ?string $model = LeadSource::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-rss';
    protected static UnitEnum|string|null $navigationGroup = 'CRM';
    protected static ?string $navigationLabel = 'Lead Sources';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    TextInput::make('name')->required()->maxLength(100),
                    TextInput::make('description')->maxLength(255),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('description')->limit(60),
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
            'index' => Pages\ListLeadSources::route('/'),
            'create' => Pages\CreateLeadSource::route('/create'),
            'edit' => Pages\EditLeadSource::route('/{record}/edit'),
        ];
    }
}
