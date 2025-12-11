<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CheckCallResource\Pages;
use App\Models\CheckCall;
use App\Support\Auth\RoleGuard;
use Filament\Forms;
use App\Models\Load;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use UnitEnum;

class CheckCallResource extends Resource
{
    protected static ?string $model = CheckCall::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-phone-arrow-up-right';

    protected static UnitEnum|string|null $navigationGroup = 'Operations';

    public static function canViewAny(): bool
    {
        return RoleGuard::hasOpsAccess(auth()->user());
    }

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Check Call')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\Select::make('load_id')
                            ->label('Load')
                            ->options(fn () => Load::query()->pluck('load_number', 'id'))
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->searchable(),
                        Forms\Components\TextInput::make('status')
                            ->required()
                            ->maxLength(64),
                        Forms\Components\TextInput::make('event_code')
                            ->maxLength(32)
                            ->helperText('ARR_PICKUP, DEP_PICKUP, ARR_DELIVERY, ISSUE_DELAY, etc.'),
                        Forms\Components\DateTimePicker::make('reported_at')->required(),
                        Forms\Components\DateTimePicker::make('recorded_at'),
                        Forms\Components\TextInput::make('lat')->numeric(),
                        Forms\Components\TextInput::make('lng')->numeric(),
                        Forms\Components\TextInput::make('location_source')->maxLength(50),
                    ]),
                    Forms\Components\Textarea::make('note')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('loadRelation.load_number')->label('Load')->searchable(),
                Tables\Columns\TextColumn::make('status')->sortable()->badge(),
                Tables\Columns\TextColumn::make('event_code')->sortable(),
                Tables\Columns\TextColumn::make('reported_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('User'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'arrived_pickup' => 'Arrived Pickup',
                        'arrived_delivery' => 'Arrived Delivery',
                        'issue' => 'Issue',
                        'delayed' => 'Delayed',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('reported_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCheckCalls::route('/'),
            'create' => Pages\CreateCheckCall::route('/create'),
            'edit' => Pages\EditCheckCall::route('/{record}/edit'),
        ];
    }
}
