<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accessorials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('load_id')->constrained('loads')->cascadeOnDelete();
            $table->string('type'); // detention, tonu, lumper, layover, other
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
            $table->index(['load_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accessorials');
    }
};
