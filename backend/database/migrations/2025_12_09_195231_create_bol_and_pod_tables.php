<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bol_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('body')->nullable(); // blade/plain template body
            $table->boolean('is_default')->default(false)->index();
            $table->timestamps();
        });

        Schema::table('loads', function (Blueprint $table): void {
            if (!Schema::hasColumn('loads', 'bol_template_id')) {
                $table->foreignId('bol_template_id')->nullable()->constrained('bol_templates')->nullOnDelete();
            }
        });

        Schema::create('pods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('load_id')->constrained('loads')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->string('signer_name')->nullable();
            $table->string('signer_title')->nullable();
            $table->dateTime('signed_at')->nullable()->index();
            $table->string('photo_path')->nullable(); // POD photo or scanned document
            $table->json('location')->nullable(); // lat/lng/accuracy snapshot
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('loads', function (Blueprint $table): void {
            if (Schema::hasColumn('loads', 'bol_template_id')) {
                $table->dropConstrainedForeignId('bol_template_id');
            }
        });

        Schema::dropIfExists('pods');
        Schema::dropIfExists('bol_templates');
    }
};
