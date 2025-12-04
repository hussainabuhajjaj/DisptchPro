<?php

namespace Database\Seeders;

use App\Settings\FooterSettings;
use App\Settings\GeneralSettings;
use App\Settings\SeoSettings;
use App\Settings\MediaSettings;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        app(GeneralSettings::class)->fill([
            'site_name' => 'DispatchPro',
            'site_title' => 'Keep your trucks loaded. Stay profitable.',
            'site_tagline' => 'Dispatch, paperwork, and load hunting handled.',
            'site_description' => 'DispatchPro handles load hunting, compliance, and paperwork so carriers and brokers stay profitable.',
            'site_timezone' => 'America/Chicago',
            'site_locale' => 'en_US',
            'logo_url' => 'media/branding/logo.png',
            'favicon_url' => 'media/branding/favicon.png',
            'contact_email' => 'hello@dispatchpro.com',
            'contact_phone' => '+1 (469) 555-2188',
            'contact_address' => '123 Fleet Ave',
            'contact_city' => 'Dallas',
            'contact_state' => 'TX',
            'contact_country' => 'USA',
            'theme_primary_color' => '#f97316',
            'theme_secondary_color' => '#0b2a45',
            'theme_accent_color' => '#2563eb',
            'theme_text_color' => '#0f172a',
        ])->save();

        app(SeoSettings::class)->fill([
            'meta_title' => 'DispatchPro | Keep your trucks loaded. Stay profitable.',
            'meta_description' => 'Expert dispatching services, paperwork, and compliance so carriers and brokers stay profitable.',
        ])->save();

        app(FooterSettings::class)->fill([
            'footer_text' => '© ' . date('Y') . ' DispatchPro. All Rights Reserved.',
            'links' => [
                ['label' => 'Privacy', 'url' => '/privacy'],
                ['label' => 'Terms', 'url' => '/terms'],
            ],
        ])->save();

        app(MediaSettings::class)->fill([
            'enforceFullSettings' => false,
            'hero_image_url' => 'media/landing/hero.jpg',
            'why_choose_us_image_url' => 'media/landing/why.jpg',
            'for_shippers_image_url' => 'media/landing/shippers.jpg',
            'for_brokers_image_url' => 'media/landing/brokers.jpg',
            'testimonial_avatar_1_url' => 'media/landing/avatars/a1.jpg',
            'testimonial_avatar_2_url' => 'media/landing/avatars/a2.jpg',
            'testimonial_avatar_3_url' => 'media/landing/avatars/a3.jpg',
        ])->save();
    }
}
