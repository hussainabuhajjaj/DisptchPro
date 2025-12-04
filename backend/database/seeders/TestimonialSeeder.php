<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'John D.',
                'title' => 'Owner-Operator',
                'quote' => 'H&A Dispatch transformed my operations. My revenue is up 30%, and I have more time to focus on driving.',
                'avatar_path' => 'media/landing/avatars/a1.jpg',
                'position' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Maria S.',
                'title' => 'Small Fleet Owner',
                'quote' => 'Consistent, high-paying loads without the stress. Highly recommended!',
                'avatar_path' => 'media/landing/avatars/a2.jpg',
                'position' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'David L.',
                'title' => 'Independent Trucker',
                'quote' => 'Paperwork handling alone is worth it. Efficient, reliable, and they understand trucking.',
                'avatar_path' => 'media/landing/avatars/a3.jpg',
                'position' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::updateOrCreate(
                ['name' => $testimonial['name'], 'title' => $testimonial['title']],
                $testimonial
            );
        }
    }
}

