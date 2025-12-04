<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trucks', function (Blueprint $table) {
            $table->id();
            $table->string('unit_number')->unique();
            $table->string('plate_number')->nullable();
            $table->string('VIN')->nullable();
            $table->string('type')->nullable();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->integer('year')->nullable();
            $table->enum('status', ['available', 'in_use', 'maintenance', 'inactive'])->default('available');
            $table->unsignedBigInteger('current_load_id')->nullable()->index();
            $table->date('next_service_date')->nullable();
            $table->integer('mileage')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trucks');
    }
};
