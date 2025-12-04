<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('carriers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('MC_number')->nullable();
            $table->string('DOT_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('dispatcher_contact')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 64)->nullable();
            $table->string('zip', 20)->nullable();
            $table->string('country', 64)->nullable();
            $table->string('insurance_company')->nullable();
            $table->string('insurance_policy_number')->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('factoring_company')->nullable();
            $table->string('factoring_email')->nullable();
            $table->enum('onboarding_status', ['new', 'pending_docs', 'approved', 'blacklisted'])->default('new');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carriers');
    }
};
