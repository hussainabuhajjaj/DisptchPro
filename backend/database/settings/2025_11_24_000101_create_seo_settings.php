<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('seo.meta_title', 'DispatchPro');
        $this->migrator->add('seo.meta_description', 'Reliable dispatch and load management platform.');
        $this->migrator->add('seo.og_image', null);
        $this->migrator->add('seo.twitter_handle', null);
        $this->migrator->add('seo.facebook_page', null);
    }
};
