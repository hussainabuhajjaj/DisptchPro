<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class FooterSettings extends Settings
{
    public string $footer_text;
    public array $links;

    public static function group(): string
    {
        return 'footer';
    }
}
