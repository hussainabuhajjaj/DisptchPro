<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('credit_balances', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 32); // client or carrier
            $table->unsignedBigInteger('entity_id');
            $table->string('source_type', 32)->nullable(); // invoice or settlement
            $table->unsignedBigInteger('source_id')->nullable();
            $table->decimal('amount', 12, 2)->default(0); // original amount
            $table->decimal('remaining', 12, 2)->default(0);
            $table->string('reason')->nullable(); // overpayment, refund, adjustment
            $table->timestamps();
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_balances');
    }
};
