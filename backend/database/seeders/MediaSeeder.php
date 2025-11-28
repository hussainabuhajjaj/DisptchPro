<?php

namespace Database\Seeders;

use App\Models\Media;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    public function run(): void
    {
        Media::updateOrCreate(
            ['id' => 1],
            [
                'hero_image_url' => 'media/landing/hero.jpg',
                'why_choose_us_image_url' => 'media/landing/why.jpg',
                'for_shippers_image_url' => 'media/landing/shippers.jpg',
                'for_brokers_image_url' => 'media/landing/brokers.jpg',
                'testimonial_avatar_1_url' => 'media/landing/avatars/a1.jpg',
                'testimonial_avatar_2_url' => 'media/landing/avatars/a2.jpg',
                'testimonial_avatar_3_url' => 'media/landing/avatars/a3.jpg',
                'enforce_full_settings' => false,
                'hero_image_meta' => ['width' => 1920, 'height' => 1080],
                'why_choose_us_image_meta' => ['width' => 1920, 'height' => 1080],
                'for_shippers_image_meta' => ['width' => 1920, 'height' => 1080],
                'for_brokers_image_meta' => ['width' => 1920, 'height' => 1080],
                'testimonial_avatar_1_meta' => ['width' => 400, 'height' => 400],
                'testimonial_avatar_2_meta' => ['width' => 400, 'height' => 400],
                'testimonial_avatar_3_meta' => ['width' => 400, 'height' => 400],
            ]
        );
    }
}
