<?php

namespace App\Filament\Pages;

use App\Settings\SeoSettings;
use Filament\Forms;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;

class SeoSettingsPage extends SettingsPage
{
    protected static string $settings = SeoSettings::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-megaphone';

    protected static \UnitEnum|string|null $navigationGroup = 'Website';

    protected static ?string $navigationLabel = 'SEO & Social';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Meta')
                ->schema([
                    TextInput::make('meta_title')->label('Meta title')->required(),
                    TextInput::make('meta_description')->label('Meta description')->required(),
                    TextInput::make('og_image')->label('OG image URL')->url()->nullable(),
                ]),
            Section::make('Social')
                ->schema([
                    TextInput::make('twitter_handle')->label('Twitter/X handle')->prefix('@')->nullable(),
                    TextInput::make('facebook_page')->label('Facebook page URL')->url()->nullable(),
                ]),
        ]);
    }
}
