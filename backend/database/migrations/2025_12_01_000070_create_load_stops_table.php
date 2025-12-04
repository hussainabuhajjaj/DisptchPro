<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('load_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('load_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->enum('type', ['pickup', 'delivery']);
            $table->string('facility_name')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 64)->nullable();
            $table->string('zip', 20)->nullable();
            $table->string('country', 64)->nullable();
            $table->dateTime('date_from')->nullable();
            $table->dateTime('date_to')->nullable();
            $table->string('appointment_time')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('load_stops');
    }
};
