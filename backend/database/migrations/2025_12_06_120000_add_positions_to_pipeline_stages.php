<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pipeline_stages', function (Blueprint $table) {
            $table->decimal('position_x', 8, 2)->nullable()->after('position');
            $table->decimal('position_y', 8, 2)->nullable()->after('position_x');
        });
    }

    public function down(): void
    {
        Schema::table('pipeline_stages', function (Blueprint $table) {
            $table->dropColumn(['position_x', 'position_y']);
        });
    }
};
