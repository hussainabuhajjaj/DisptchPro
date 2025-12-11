<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PodResource\Pages;
use App\Models\Pod;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class PodResource extends Resource
{
    protected static ?string $model = Pod::class;
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static UnitEnum|string|null $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 91;

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Forms\Components\Select::make('load_id')
                ->relationship('loadRelation', 'load_number')
                ->searchable()
                ->required(),
            Forms\Components\Select::make('driver_id')
                ->relationship('driver', 'name')
                ->searchable()
                ->nullable(),
            Forms\Components\TextInput::make('signer_name'),
            Forms\Components\TextInput::make('signer_title'),
            Forms\Components\DateTimePicker::make('signed_at'),
            Forms\Components\FileUpload::make('photo_path')
                ->label('POD photo')
                ->directory('pods')
                ->image()
                ->maxSize(20480),
            Forms\Components\KeyValue::make('location')
                ->label('Location snapshot')
                ->keyLabel('key')
                ->valueLabel('value')
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('loadRelation.load_number')->label('Load'),
                Tables\Columns\TextColumn::make('driver.name')->label('Driver'),
                Tables\Columns\TextColumn::make('signer_name')->label('Signer'),
                Tables\Columns\TextColumn::make('signed_at')->since(),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Uploaded'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPods::route('/'),
            'create' => Pages\CreatePod::route('/create'),
            'edit' => Pages\EditPod::route('/{record}/edit'),
        ];
    }
}
