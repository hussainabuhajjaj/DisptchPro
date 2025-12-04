<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['shipper', 'broker', 'direct_client']);
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 64)->nullable();
            $table->string('zip', 20)->nullable();
            $table->string('country', 64)->nullable();
            $table->string('payment_terms')->nullable();
            $table->decimal('credit_limit', 12, 2)->nullable();
            $table->string('tax_id')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
