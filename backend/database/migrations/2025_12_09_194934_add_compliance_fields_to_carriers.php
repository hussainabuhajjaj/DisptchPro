<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carriers', function (Blueprint $table): void {
            if (!Schema::hasColumn('carriers', 'legal_name')) {
                $table->string('legal_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('carriers', 'dba_name')) {
                $table->string('dba_name')->nullable()->after('legal_name');
            }
            if (!Schema::hasColumn('carriers', 'usd_ot_number')) {
                $table->string('usd_ot_number')->nullable()->index()->after('dba_name');
            }
            if (!Schema::hasColumn('carriers', 'mc_number')) {
                $table->string('mc_number')->nullable()->index()->after('usd_ot_number');
            }
            if (!Schema::hasColumn('carriers', 'safety_rating')) {
                $table->string('safety_rating')->nullable()->after('mc_number');
            }
            if (!Schema::hasColumn('carriers', 'safer_profile_url')) {
                $table->string('safer_profile_url')->nullable()->after('safety_rating');
            }
            if (!Schema::hasColumn('carriers', 'insurance_primary_name')) {
                $table->string('insurance_primary_name')->nullable()->after('safer_profile_url');
            }
            if (!Schema::hasColumn('carriers', 'insurance_policy_number')) {
                $table->string('insurance_policy_number')->nullable()->after('insurance_primary_name');
            }
            if (!Schema::hasColumn('carriers', 'insurance_coverage_types')) {
                $table->json('insurance_coverage_types')->nullable()->after('insurance_policy_number');
            }
            if (!Schema::hasColumn('carriers', 'insurance_limits')) {
                $table->json('insurance_limits')->nullable()->after('insurance_coverage_types');
            }
            if (!Schema::hasColumn('carriers', 'insurance_expires_at')) {
                $table->dateTime('insurance_expires_at')->nullable()->after('insurance_limits')->index();
            }
            if (!Schema::hasColumn('carriers', 'coi_document_path')) {
                $table->string('coi_document_path')->nullable()->after('insurance_expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('carriers', function (Blueprint $table): void {
            $table->dropColumn([
                'legal_name',
                'dba_name',
                'usd_ot_number',
                'mc_number',
                'safety_rating',
                'safer_profile_url',
                'insurance_primary_name',
                'insurance_policy_number',
                'insurance_coverage_types',
                'insurance_limits',
                'insurance_expires_at',
                'coi_document_path',
            ]);
        });
    }
};
