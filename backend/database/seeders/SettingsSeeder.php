<?php

namespace Database\Seeders;

use App\Settings\FooterSettings;
use App\Settings\GeneralSettings;
use App\Settings\SeoSettings;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        app(GeneralSettings::class)->fill([
            'site_name' => 'DispatchPro',
        ])->save();

        app(SeoSettings::class)->fill([
            'meta_title' => 'DispatchPro',
            'meta_description' => 'Logistics platform',
        ])->save();

        app(FooterSettings::class)->fill([
            'footer_text' => '© DispatchPro',
            'links' => [
                ['label' => 'Privacy', 'url' => '/privacy'],
                ['label' => 'Terms', 'url' => '/terms'],
            ],
        ])->save();
    }
}
