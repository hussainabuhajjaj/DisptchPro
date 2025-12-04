<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loads', function (Blueprint $table) {
            if (!Schema::hasColumn('loads', 'created_at')) {
                return;
            }
            $table->index(['created_at', 'id'], 'loads_created_at_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('loads', function (Blueprint $table) {
            $table->dropIndex('loads_created_at_id_index');
        });
    }
};
