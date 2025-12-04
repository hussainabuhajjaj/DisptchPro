<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('load_id')->nullable()->constrained()->nullOnDelete();
            $table->string('settlement_type', 16); // carrier or driver
            $table->unsignedBigInteger('entity_id')->nullable(); // carrier_id or driver_id
            $table->date('issue_date')->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('status', 32)->default('draft'); // draft, issued, paid, void
            $table->timestamps();
            $table->index(['settlement_type', 'entity_id', 'status']);
        });

        Schema::create('settlement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('settlement_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('rate', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('settlement_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('settlement_id')->constrained()->cascadeOnDelete();
            $table->date('paid_at')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('method', 64)->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_payments');
        Schema::dropIfExists('settlement_items');
        Schema::dropIfExists('settlements');
    }
};
