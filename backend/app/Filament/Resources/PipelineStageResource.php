<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PipelineStageResource\Pages;
use App\Models\PipelineStage;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PipelineStageResource extends Resource
{
    protected static ?string $model = PipelineStage::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-bars-3-bottom-left';
    protected static UnitEnum|string|null $navigationGroup = 'CRM';
    protected static ?string $navigationLabel = 'Pipeline Stages';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('name')->required()->maxLength(100),
                        TextInput::make('position')->numeric()->default(0),
                        Toggle::make('is_default')->label('Default stage'),
                    ]),
                    TextInput::make('description')->maxLength(255),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('description')->limit(40),
                TextColumn::make('position')->sortable(),
                IconColumn::make('is_default')->label('Default')->boolean(),
            ])
            ->defaultSort('position')
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
            'index' => Pages\ListPipelineStages::route('/'),
            'create' => Pages\CreatePipelineStage::route('/create'),
            'edit' => Pages\EditPipelineStage::route('/{record}/edit'),
        ];
    }
}
