<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SeoSettings extends Settings
{
    public string $meta_title;
    public string $meta_description;
    public ?string $og_image;
    public ?string $twitter_handle;
    public ?string $facebook_page;

    public static function group(): string
    {
        return 'seo';
    }
}
