<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class GeneralSettingsPage extends SettingsPage
{
    protected static string $settings = GeneralSettings::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static \UnitEnum|string|null $navigationGroup = 'Website';

    protected static ?string $navigationLabel = 'General Settings';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Brand')
                ->schema([
                    TextInput::make('site_name')->label('Site name')->required(),
                    TextInput::make('site_title')->label('Site title')->required(),
                    TextInput::make('site_tagline')->label('Tagline')->required(),
                    Textarea::make('site_description')->label('Description')->rows(3)->required(),
                    FileUpload::make('logo_url')->label('Logo ')->nullable(),
                    FileUpload::make('favicon_url')->label('Favicon ')->nullable(),
                ]),
            Section::make('Localization')
                ->schema([
                    TextInput::make('site_timezone')
                        ->label('Timezone')
                        ->datalist(timezone_identifiers_list())
                        ->required(),
                    TextInput::make('site_locale')
                        ->label('Locale')
                        ->placeholder('en_US')
                        ->required(),
                ])
                ->columns(2),
            Section::make('Contact')
                ->schema([
                    TextInput::make('contact_email')->email()->required(),
                    TextInput::make('contact_phone')->nullable(),
                    TextInput::make('contact_address')->nullable(),
                    TextInput::make('contact_city')->nullable(),
                    TextInput::make('contact_state')->nullable(),
                    TextInput::make('contact_country')->nullable(),
                ])
                ->columns(2),
            Section::make('Theme')
                ->schema([
                    ColorPicker::make('theme_primary_color')->label('Primary')->required(),
                    ColorPicker::make('theme_secondary_color')->label('Secondary')->required(),
                    ColorPicker::make('theme_accent_color')->label('Accent')->required(),
                    ColorPicker::make('theme_text_color')->label('Text')->required(),
                ])
                ->columns(2),
        ]);
    }
}
