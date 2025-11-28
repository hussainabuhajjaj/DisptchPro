<?php

namespace App\Filament\Pages;

use App\Settings\MediaSettings;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;

class MediaSettingsPage extends SettingsPage
{
    protected static string $settings = MediaSettings::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-photo';

    protected static \UnitEnum|string|null $navigationGroup = 'Website';

    protected static ?string $navigationLabel = 'Media & Images';

    protected static bool $shouldRegisterNavigation = false;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Hero & Highlights')
                ->columns(2)
                ->schema([
                    FileUpload::make('hero_image_url')
                        ->label('Hero background')
                        ->image()
                        ->directory('media/landing')
                        ->visibility('public')
                        ->maxSize(8096),
                    FileUpload::make('why_choose_us_image_url')
                        ->label('Why choose us')
                        ->image()
                        ->directory('media/landing')
                        ->visibility('public')
                        ->maxSize(8096),
                ]),
            Section::make('Audience Sections')
                ->columns(2)
                ->schema([
                    FileUpload::make('for_shippers_image_url')
                        ->label('For shippers')
                        ->image()
                        ->directory('media/landing')
                        ->visibility('public')
                        ->maxSize(8096),
                    FileUpload::make('for_brokers_image_url')
                        ->label('For brokers')
                        ->image()
                        ->directory('media/landing')
                        ->visibility('public')
                        ->maxSize(8096),
                ]),
            Section::make('Testimonials')
                ->columns(3)
                ->schema([
                    FileUpload::make('testimonial_avatar_1_url')
                        ->label('Testimonial avatar 1')
                        ->image()
                        ->circleCropper()
                        ->directory('media/landing/avatars')
                        ->visibility('public')
                        ->maxSize(8096),
                    FileUpload::make('testimonial_avatar_2_url')
                        ->label('Testimonial avatar 2')
                        ->image()
                        ->circleCropper()
                        ->directory('media/landing/avatars')
                        ->visibility('public')
                        ->maxSize(8096),
                    FileUpload::make('testimonial_avatar_3_url')
                        ->label('Testimonial avatar 3')
                        ->image()
                        ->circleCropper()
                        ->directory('media/landing/avatars')
                        ->visibility('public')
                        ->maxSize(8096),
                ]),
        ]);
    }
}
