<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('check_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('load_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 32)->nullable();
            $table->text('note')->nullable();
            $table->dateTime('reported_at')->nullable();
            $table->timestamps();

            $table->index(['load_id', 'reported_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_calls');
    }
};
