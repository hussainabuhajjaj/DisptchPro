<?php

namespace App\Filament\Pages;

use App\Settings\FooterSettings;
use Filament\Forms;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;

class FooterSettingsPage extends SettingsPage
{
    protected static string $settings = FooterSettings::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-link';

    protected static \UnitEnum|string|null $navigationGroup = 'Website';

    protected static ?string $navigationLabel = 'Footer';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Footer')
                ->schema([
                    TextInput::make('footer_text')->label('Footer text')->required(),
                    Repeater::make('links')
                        ->schema([
                            TextInput::make('label')->required(),
                            TextInput::make('url')->url()->required(),
                        ])
                        ->label('Links')
                        ->addActionLabel('Add link')
                        ->reorderable()
                        ->minItems(0),
                ]),
        ]);
    }
}
