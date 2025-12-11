<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('driver_api_tokens')) {
            Schema::create('driver_api_tokens', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
                $table->string('name')->nullable();
                $table->string('token_hash', 128)->unique();
                $table->string('token_prefix', 12)->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent')->nullable();
                $table->dateTime('last_used_at')->nullable();
                $table->dateTime('expires_at')->nullable();
                $table->dateTime('revoked_at')->nullable();
                $table->timestamps();

                $table->index(['driver_id', 'revoked_at']);
                $table->index(['expires_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_api_tokens');
    }
};
