<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('load_locations', function (Blueprint $table): void {
            if (!Schema::hasColumn('load_locations', 'accuracy_m')) {
                $table->decimal('accuracy_m', 8, 2)->nullable()->after('heading');
            }
            if (!Schema::hasColumn('load_locations', 'source')) {
                $table->string('source', 50)->nullable()->after('accuracy_m');
            }
            if (!Schema::hasColumn('load_locations', 'is_valid')) {
                $table->boolean('is_valid')->default(true)->after('source');
            }
            if (!Schema::hasColumn('load_locations', 'track_id')) {
                $table->string('track_id', 64)->nullable()->after('is_valid');
            }

            $table->index(['load_id', 'recorded_at']);
            $table->index(['driver_id', 'recorded_at']);
        });

        Schema::table('loads', function (Blueprint $table): void {
            $table->index('driver_id');
            $table->index('dispatcher_id');
            $table->index('status');
            $table->index('last_location_at');
        });
    }

    public function down(): void
    {
        Schema::table('load_locations', function (Blueprint $table): void {
            if (Schema::hasColumn('load_locations', 'accuracy_m')) {
                $table->dropColumn(['accuracy_m', 'source', 'is_valid', 'track_id']);
            }
            $table->dropIndex('load_locations_load_id_recorded_at_index');
            $table->dropIndex('load_locations_driver_id_recorded_at_index');
        });

        Schema::table('loads', function (Blueprint $table): void {
            $table->dropIndex('loads_driver_id_index');
            $table->dropIndex('loads_dispatcher_id_index');
            $table->dropIndex('loads_status_index');
            $table->dropIndex('loads_last_location_at_index');
        });
    }
};
