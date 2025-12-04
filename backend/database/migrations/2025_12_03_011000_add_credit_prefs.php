<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'auto_apply_credit')) {
                $table->boolean('auto_apply_credit')->default(false)->after('credit_limit');
            }
            if (!Schema::hasColumn('clients', 'credit_expiry_days')) {
                $table->integer('credit_expiry_days')->nullable()->after('auto_apply_credit');
            }
        });

        Schema::table('carriers', function (Blueprint $table) {
            if (!Schema::hasColumn('carriers', 'auto_apply_credit')) {
                $table->boolean('auto_apply_credit')->default(false)->after('payment_terms');
            }
            if (!Schema::hasColumn('carriers', 'credit_expiry_days')) {
                $table->integer('credit_expiry_days')->nullable()->after('auto_apply_credit');
            }
        });

        Schema::table('credit_balances', function (Blueprint $table) {
            if (!Schema::hasColumn('credit_balances', 'expires_at')) {
                $table->date('expires_at')->nullable()->after('remaining');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'auto_apply_credit')) {
                $table->dropColumn('auto_apply_credit');
            }
            if (Schema::hasColumn('clients', 'credit_expiry_days')) {
                $table->dropColumn('credit_expiry_days');
            }
        });

        Schema::table('carriers', function (Blueprint $table) {
            if (Schema::hasColumn('carriers', 'auto_apply_credit')) {
                $table->dropColumn('auto_apply_credit');
            }
            if (Schema::hasColumn('carriers', 'credit_expiry_days')) {
                $table->dropColumn('credit_expiry_days');
            }
        });

        Schema::table('credit_balances', function (Blueprint $table) {
            if (Schema::hasColumn('credit_balances', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
        });
    }
};
