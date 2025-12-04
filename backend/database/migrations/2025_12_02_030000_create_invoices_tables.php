<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('load_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
                $table->string('invoice_number')->nullable();
                $table->date('issue_date')->nullable();
                $table->date('due_date')->nullable();
                $table->decimal('total', 12, 2)->default(0);
                $table->decimal('balance', 12, 2)->default(0);
                $table->string('status', 32)->default('draft'); // draft, sent, partial, paid, void
                $table->timestamps();
                $table->index(['client_id', 'status']);
            });
        }

        if (!Schema::hasTable('invoice_items')) {
            Schema::create('invoice_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
                $table->string('description');
                $table->integer('quantity')->default(1);
                $table->decimal('rate', 12, 2)->default(0);
                $table->decimal('amount', 12, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('invoice_payments')) {
            Schema::create('invoice_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
                $table->date('paid_at')->nullable();
                $table->decimal('amount', 12, 2)->default(0);
                $table->string('method', 64)->nullable();
                $table->string('reference')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
