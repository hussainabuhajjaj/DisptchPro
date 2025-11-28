<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Testimonial;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Website';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('title')
                    ->label('Role / Company')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('avatar_path')
                    ->label('Avatar')
                    ->image()
                    ->directory('media/landing/avatars')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->imageEditor()
                    ->circleCropper(),
                Forms\Components\Textarea::make('quote')
                    ->required()
                    ->rows(4),
                Forms\Components\TextInput::make('position')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers show first.')
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->label('Active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('title')->label('Role')->searchable(),
                Tables\Columns\TextColumn::make('is_active')
                    ->label('Active')->badge()
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
                Tables\Columns\TextColumn::make('position')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([])
            ->toolbarActions([
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
            'index' => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
