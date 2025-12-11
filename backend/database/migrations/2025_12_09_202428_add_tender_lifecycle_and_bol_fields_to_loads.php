<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loads', function (Blueprint $table): void {
            if (!Schema::hasColumn('loads', 'tendered_at')) {
                $table->dateTime('tendered_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('loads', 'carrier_accepted_at')) {
                $table->dateTime('carrier_accepted_at')->nullable()->after('tendered_at');
            }
            if (!Schema::hasColumn('loads', 'carrier_rejected_at')) {
                $table->dateTime('carrier_rejected_at')->nullable()->after('carrier_accepted_at');
            }
            if (!Schema::hasColumn('loads', 'driver_acknowledged_at')) {
                $table->dateTime('driver_acknowledged_at')->nullable()->after('carrier_rejected_at');
            }
            if (!Schema::hasColumn('loads', 'bol_path')) {
                $table->string('bol_path')->nullable()->after('bol_template_id');
            }
            if (!Schema::hasColumn('loads', 'bol_generated_at')) {
                $table->dateTime('bol_generated_at')->nullable()->after('bol_path');
            }
            if (!Schema::hasColumn('loads', 'pod_id')) {
                $table->foreignId('pod_id')->nullable()->constrained('pods')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('loads', function (Blueprint $table): void {
            $table->dropColumn([
                'tendered_at',
                'carrier_accepted_at',
                'carrier_rejected_at',
                'driver_acknowledged_at',
                'bol_path',
                'bol_generated_at',
            ]);
            if (Schema::hasColumn('loads', 'pod_id')) {
                $table->dropConstrainedForeignId('pod_id');
            }
        });
    }
};
