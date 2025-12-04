<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MediaSettings extends Settings
{
    public bool $enforceFullSettings = false;

    public ?string $hero_image_url = null;
    public ?string $why_choose_us_image_url = null;
    public ?string $for_shippers_image_url = null;
    public ?string $for_brokers_image_url = null;
    public ?string $testimonial_avatar_1_url = null;
    public ?string $testimonial_avatar_2_url = null;
    public ?string $testimonial_avatar_3_url = null;

    public static function group(): string
    {
        return 'media';
    }

    public static function defaults(): array
    {
        return [
            'enforceFullSettings' => false,
            'hero_image_url' => null,
            'why_choose_us_image_url' => null,
            'for_shippers_image_url' => null,
            'for_brokers_image_url' => null,
            'testimonial_avatar_1_url' => null,
            'testimonial_avatar_2_url' => null,
            'testimonial_avatar_3_url' => null,
        ];
    }
}
