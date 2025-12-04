<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trailers', function (Blueprint $table) {
            $table->id();
            $table->string('trailer_number')->unique();
            $table->string('plate_number')->nullable();
            $table->string('VIN')->nullable();
            $table->string('type');
            $table->string('length')->nullable();
            $table->integer('max_weight')->nullable();
            $table->json('reefer_settings')->nullable();
            $table->enum('status', ['available', 'in_use', 'maintenance', 'inactive'])->default('available');
            $table->date('next_service_date')->nullable();
            $table->integer('mileage')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trailers');
    }
};
