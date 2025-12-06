<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pipeline_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_stage_id')->constrained('pipeline_stages')->cascadeOnDelete();
            $table->foreignId('to_stage_id')->constrained('pipeline_stages')->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->json('actions')->nullable(); // e.g., {"type":"email","template":"new-lead"}
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipeline_transitions');
    }
};
