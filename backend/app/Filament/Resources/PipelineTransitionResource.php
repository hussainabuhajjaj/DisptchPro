<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PipelineTransitionResource\Pages;
use App\Models\PipelineStage;
use App\Models\PipelineTransition;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\JsonEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class PipelineTransitionResource extends Resource
{
    protected static ?string $model = PipelineTransition::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static UnitEnum|string|null $navigationGroup = 'CRM';
    protected static ?string $navigationLabel = 'Pipeline Transitions';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    Select::make('from_stage_id')
                        ->label('From stage')
                        ->options(fn () => PipelineStage::orderBy('position')->pluck('name', 'id'))
                        ->required()
                        ->native(false),
                    Select::make('to_stage_id')
                        ->label('To stage')
                        ->options(fn () => PipelineStage::orderBy('position')->pluck('name', 'id'))
                        ->required()
                        ->native(false),
                    TextInput::make('label')->label('Label'),
                    JsonEditor::make('actions')
                        ->label('Actions (JSON)')
                        ->helperText('Define automations, e.g. {"type":"email","template":"new-lead"}')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fromStage.name')->label('From')->sortable(),
                Tables\Columns\TextColumn::make('toStage.name')->label('To')->sortable(),
                Tables\Columns\TextColumn::make('label')->sortable(),
                Tables\Columns\TextColumn::make('actions')->limit(40),
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
            'index' => Pages\ListPipelineTransitions::route('/'),
            'create' => Pages\CreatePipelineTransition::route('/create'),
            'edit' => Pages\EditPipelineTransition::route('/{record}/edit'),
        ];
    }
}
