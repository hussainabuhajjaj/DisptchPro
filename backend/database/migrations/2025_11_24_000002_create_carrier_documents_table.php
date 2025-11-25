<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('carrier_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('draft_id')->constrained('carrier_drafts')->cascadeOnDelete();
            $table->string('type');
            $table->string('path');
            $table->string('file_name')->nullable();
            $table->string('status')->default('pending');
            $table->text('reviewer_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrier_documents');
    }
};
