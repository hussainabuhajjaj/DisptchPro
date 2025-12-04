<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('carriers', function (Blueprint $table) {
            $table->string('w9_path')->nullable();
            $table->string('coi_path')->nullable();
            $table->string('carrier_packet_path')->nullable();
            $table->string('contract_path')->nullable();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->string('contract_path')->nullable();
        });

        Schema::table('loads', function (Blueprint $table) {
            $table->string('rate_con_path')->nullable();
            $table->string('pod_path')->nullable();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('pdf_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('carriers', function (Blueprint $table) {
            $table->dropColumn(['w9_path', 'coi_path', 'carrier_packet_path', 'contract_path']);
        });
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['contract_path']);
        });
        Schema::table('loads', function (Blueprint $table) {
            $table->dropColumn(['rate_con_path', 'pod_path']);
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['pdf_path']);
        });
    }
};
