<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name;
    public string $site_title;
    public string $site_tagline;
    public string $site_description;
    public string $site_timezone;
    public string $site_locale;
    public ?string $logo_url;
    public ?string $favicon_url;

    public string $contact_email;
    public ?string $contact_phone;
    public ?string $contact_address;
    public ?string $contact_city;
    public ?string $contact_state;
    public ?string $contact_country;

    public string $theme_primary_color;
    public string $theme_secondary_color;
    public string $theme_accent_color;
    public string $theme_text_color;

    public static function group(): string
    {
        return 'general';
    }
}
