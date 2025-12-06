<?php

namespace App\Filament\Resources;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;


use Spatie\Permission\Models\Role;

use UnitEnum;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-key';

    protected static UnitEnum|string|null $navigationGroup = 'Admin';

    protected static ?string $navigationLabel = 'Roles';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Role')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Name')
                        ->required()
                        ->unique(ignoreRecord: true),
                    TextInput::make('guard_name')
                        ->label('Guard')
                        ->default('web')
                        ->required(),
                    Select::make('permissions')
                        ->label('Permissions')
                        ->relationship('permissions', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('guard_name')
                    ->label('Guard')
                    ->sortable(),
                Tables\Columns\TextColumn::make('permissions.name')
                    ->label('Permissions')
                    ->badge()
                    ->limitList(3)
                    ->wrap(),
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
            'index' => RoleResource\Pages\ListRoles::route('/'),
            'create' => RoleResource\Pages\CreateRole::route('/create'),
            'edit' => RoleResource\Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
