<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('hero_image_url')->nullable();
            $table->string('why_choose_us_image_url')->nullable();
            $table->string('for_shippers_image_url')->nullable();
            $table->string('for_brokers_image_url')->nullable();
            $table->string('testimonial_avatar_1_url')->nullable();
            $table->string('testimonial_avatar_2_url')->nullable();
            $table->string('testimonial_avatar_3_url')->nullable();
            $table->boolean('enforce_full_settings')->default(false);
            $table->timestamps();
        });

        // Attempt to backfill from existing settings entry if present.
        if (Schema::hasTable('settings')) {
            $existing = DB::table('settings')
                ->where('group', 'media')
                ->where('name', 'media')
                ->first();

            if ($existing) {
                $payload = json_decode($existing->payload, true) ?? [];

                DB::table('media')->insert([
                    'id' => 1,
                    'hero_image_url' => $payload['hero_image_url'] ?? null,
                    'why_choose_us_image_url' => $payload['why_choose_us_image_url'] ?? null,
                    'for_shippers_image_url' => $payload['for_shippers_image_url'] ?? null,
                    'for_brokers_image_url' => $payload['for_brokers_image_url'] ?? null,
                    'testimonial_avatar_1_url' => $payload['testimonial_avatar_1_url'] ?? null,
                    'testimonial_avatar_2_url' => $payload['testimonial_avatar_2_url'] ?? null,
                    'testimonial_avatar_3_url' => $payload['testimonial_avatar_3_url'] ?? null,
                    'enforce_full_settings' => (bool) ($payload['enforceFullSettings'] ?? false),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
