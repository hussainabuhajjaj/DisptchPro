<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->json('hero_image_meta')->nullable();
            $table->json('why_choose_us_image_meta')->nullable();
            $table->json('for_shippers_image_meta')->nullable();
            $table->json('for_brokers_image_meta')->nullable();
            $table->json('testimonial_avatar_1_meta')->nullable();
            $table->json('testimonial_avatar_2_meta')->nullable();
            $table->json('testimonial_avatar_3_meta')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn([
                'hero_image_meta',
                'why_choose_us_image_meta',
                'for_shippers_image_meta',
                'for_brokers_image_meta',
                'testimonial_avatar_1_meta',
                'testimonial_avatar_2_meta',
                'testimonial_avatar_3_meta',
            ]);
        });
    }
};
