<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Convert enum to string to support additional stop types
        Schema::table('load_stops', function (Blueprint $table) {
            $table->string('type', 32)->default('pickup')->change();
        });
    }

    public function down(): void
    {
        Schema::table('load_stops', function (Blueprint $table) {
            $table->enum('type', ['pickup', 'delivery'])->default('pickup')->change();
        });
    }
};
