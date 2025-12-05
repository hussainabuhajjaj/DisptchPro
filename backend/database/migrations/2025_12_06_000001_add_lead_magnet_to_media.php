<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->string('lead_magnet_url')->nullable()->after('testimonial_avatar_3_url');
            $table->json('lead_magnet_meta')->nullable()->after('testimonial_avatar_3_meta');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn(['lead_magnet_url', 'lead_magnet_meta']);
        });
    }
};
