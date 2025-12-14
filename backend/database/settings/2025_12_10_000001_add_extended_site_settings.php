<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('general.logo_dark_url', null);
        $this->migrator->add('general.canonical_url', null);
        $this->migrator->add('general.support_text', null);
        $this->migrator->add('general.topbar_text', null);
        $this->migrator->add('general.primary_cta_label', null);
        $this->migrator->add('general.primary_cta_url', null);
        $this->migrator->add('general.secondary_cta_label', null);
        $this->migrator->add('general.secondary_cta_url', null);
        $this->migrator->add('general.book_a_call_url', null);
        $this->migrator->add('general.header_links', []);
        $this->migrator->add('general.trust_logos', []);
    }
};
