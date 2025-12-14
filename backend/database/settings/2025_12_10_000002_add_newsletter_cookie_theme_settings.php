<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('general.theme_default_mode', 'system'); // light|dark|system
        $this->migrator->add('general.theme_allow_mode_toggle', true);

        $this->migrator->add('general.newsletter_enabled', true);
        $this->migrator->add('general.newsletter_form_action', 'https://hadispatch.com/api/newsletter');
        $this->migrator->add('general.newsletter_consent_text', 'By subscribing you agree to receive updates from H&A Dispatch.');

        $this->migrator->add('general.cookie_banner_enabled', true);
        $this->migrator->add('general.cookie_message', 'We use cookies to improve your experience and analyze traffic.');
        $this->migrator->add('general.cookie_cta_text', 'Got it');
        $this->migrator->add('general.cookie_policy_url', '/privacy');
    }
};
