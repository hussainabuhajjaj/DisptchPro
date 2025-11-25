<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LandingSectionResource\Pages;
use App\Models\LandingSection;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LandingSectionResource extends Resource
{
    protected static ?string $model = LandingSection::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-group';

    protected static \UnitEnum|string|null $navigationGroup = 'Website';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->datalist([
                        'hero',
                        'features',
                        'kpis',
                        'load-board',
                        'testimonials',
                        'blog',
                        'cta',
                    ])
                    ->helperText('Example: hero, features, kpis, load-board'),
                Forms\Components\TextInput::make('title')
                    ->required(),
                Forms\Components\TextInput::make('subtitle')
                    ->maxLength(255),
                Forms\Components\KeyValue::make('content')
                    ->label('Content JSON')
                    ->reorderable()
                    ->addButtonLabel('Add field')
                    ->helperText('Flexible payload: e.g., {"headline": "...", "items": [...]}. For cards, use items; for KPIs, use metrics array.'),
                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->label('Active'),
                Forms\Components\TextInput::make('position')
                    ->numeric()
                    ->default(1)
                    ->helperText('Controls ordering on landing page.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('slug')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('title')->searchable()->limit(40),
                Tables\Columns\BooleanColumn::make('is_active')->label('Active'),
                Tables\Columns\TextColumn::make('position')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
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
            'index' => Pages\ListLandingSections::route('/'),
            'create' => Pages\CreateLandingSection::route('/create'),
            'edit' => Pages\EditLandingSection::route('/{record}/edit'),
        ];
    }
}
