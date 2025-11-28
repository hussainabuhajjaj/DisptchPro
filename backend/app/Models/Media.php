<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    protected $fillable = [
        'hero_image_url',
        'why_choose_us_image_url',
        'for_shippers_image_url',
        'for_brokers_image_url',
        'testimonial_avatar_1_url',
        'testimonial_avatar_2_url',
        'testimonial_avatar_3_url',
        'hero_image_meta',
        'why_choose_us_image_meta',
        'for_shippers_image_meta',
        'for_brokers_image_meta',
        'testimonial_avatar_1_meta',
        'testimonial_avatar_2_meta',
        'testimonial_avatar_3_meta',
        'enforce_full_settings',
    ];

    protected function casts(): array
    {
        return [
            'hero_image_meta' => 'array',
            'why_choose_us_image_meta' => 'array',
            'for_shippers_image_meta' => 'array',
            'for_brokers_image_meta' => 'array',
            'testimonial_avatar_1_meta' => 'array',
            'testimonial_avatar_2_meta' => 'array',
            'testimonial_avatar_3_meta' => 'array',
            'enforce_full_settings' => 'bool',
        ];
    }
}
