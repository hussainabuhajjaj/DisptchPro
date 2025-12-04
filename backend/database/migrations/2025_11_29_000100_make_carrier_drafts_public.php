<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('carrier_drafts', function (Blueprint $table) {
            // Allow nullable user for public submissions and add a reference code for lookup
            $table->dropForeign(['user_id']);
            $table->string('reference_code', 20)->nullable()->unique()->after('id');
        });

        // Make user_id nullable without requiring doctrine/dbal
        DB::statement('ALTER TABLE carrier_drafts MODIFY user_id BIGINT UNSIGNED NULL');

        Schema::table('carrier_drafts', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        // Backfill reference codes for existing drafts
        $existing = DB::table('carrier_drafts')->whereNull('reference_code')->get();
        foreach ($existing as $draft) {
            do {
                $code = strtoupper(Str::random(10));
            } while (
                DB::table('carrier_drafts')
                    ->where('reference_code', $code)
                    ->exists()
            );

            DB::table('carrier_drafts')
                ->where('id', $draft->id)
                ->update(['reference_code' => $code]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carrier_drafts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('reference_code');
        });

        DB::statement('ALTER TABLE carrier_drafts MODIFY user_id BIGINT UNSIGNED NOT NULL');

        Schema::table('carrier_drafts', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
