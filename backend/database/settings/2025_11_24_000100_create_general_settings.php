<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_name', 'DispatchPro');
        $this->migrator->add('general.site_title', 'DispatchPro');
        $this->migrator->add('general.site_tagline', 'Your logistics partner');
        $this->migrator->add('general.site_description', 'Reliable dispatch and load management platform.');
        $this->migrator->add('general.site_timezone', 'America/New_York');
        $this->migrator->add('general.site_locale', 'en_US');
        $this->migrator->add('general.logo_url', null);
        $this->migrator->add('general.favicon_url', null);
        $this->migrator->add('general.contact_email', 'support@example.com');
        $this->migrator->add('general.contact_phone', null);
        $this->migrator->add('general.contact_address', null);
        $this->migrator->add('general.contact_city', null);
        $this->migrator->add('general.contact_state', null);
        $this->migrator->add('general.contact_country', null);
        $this->migrator->add('general.theme_primary_color', '#0f172a');
        $this->migrator->add('general.theme_secondary_color', '#1f2937');
        $this->migrator->add('general.theme_accent_color', '#2563eb');
        $this->migrator->add('general.theme_text_color', '#0f172a');
    }
};
