<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'load_id')) {
                $table->foreignId('load_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('invoices', 'issue_date')) {
                $table->date('issue_date')->nullable()->after('invoice_date');
            }
            if (!Schema::hasColumn('invoices', 'balance')) {
                $table->decimal('balance', 12, 2)->default(0)->after('total');
            }
        });

        // Backfill balance from legacy balance_due if present
        if (Schema::hasColumn('invoices', 'balance') && Schema::hasColumn('invoices', 'balance_due')) {
            DB::table('invoices')->whereNull('balance')->orWhere('balance', 0)->update([
                'balance' => DB::raw('balance_due'),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'load_id')) {
                $table->dropConstrainedForeignId('load_id');
            }
            if (Schema::hasColumn('invoices', 'issue_date')) {
                $table->dropColumn('issue_date');
            }
            if (Schema::hasColumn('invoices', 'balance')) {
                $table->dropColumn('balance');
            }
        });
    }
};
