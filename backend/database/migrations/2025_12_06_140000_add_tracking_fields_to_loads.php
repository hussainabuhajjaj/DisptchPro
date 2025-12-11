<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('loads', function (Blueprint $table) {
            $table->decimal('last_lat', 10, 7)->nullable()->after('route_duration_hr');
            $table->decimal('last_lng', 10, 7)->nullable()->after('last_lat');
            $table->timestamp('last_location_at')->nullable()->after('last_lng');
            $table->integer('last_eta_minutes')->nullable()->after('last_location_at');
        });
    }

    public function down(): void
    {
        Schema::table('loads', function (Blueprint $table) {
            $table->dropColumn(['last_lat', 'last_lng', 'last_location_at', 'last_eta_minutes']);
        });
    }
};
