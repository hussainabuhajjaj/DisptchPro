<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('loads', function (Blueprint $table) {
            $table->dateTime('pickup_actual_at')->nullable()->after('status');
            $table->dateTime('delivery_actual_at')->nullable()->after('pickup_actual_at');
        });
    }

    public function down(): void
    {
        Schema::table('loads', function (Blueprint $table) {
            $table->dropColumn(['pickup_actual_at', 'delivery_actual_at']);
        });
    }
};
