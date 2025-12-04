<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loads', function (Blueprint $table) {
            $table->id();
            $table->string('load_number')->unique();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('carrier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('truck_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('trailer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('dispatcher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('trailer_type')->nullable();
            $table->decimal('rate_to_client', 12, 2)->default(0);
            $table->decimal('rate_to_carrier', 12, 2)->default(0);
            $table->decimal('fuel_surcharge', 12, 2)->default(0);
            $table->json('accessorial_charges')->nullable();
            $table->integer('distance_miles')->nullable();
            $table->integer('estimated_distance')->nullable();
            $table->string('commodity')->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->integer('pieces')->nullable();
            $table->string('equipment_requirements')->nullable();
            $table->json('reference_numbers')->nullable();
            $table->enum('status', ['draft', 'posted', 'assigned', 'in_transit', 'delivered', 'completed', 'cancelled'])->default('draft');
            $table->text('internal_notes')->nullable();
            $table->text('driver_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loads');
    }
};
