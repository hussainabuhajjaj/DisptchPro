<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'whatsapp')) {
                $table->string('whatsapp')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('leads', 'preferred_contact')) {
                $table->string('preferred_contact')->nullable()->after('whatsapp');
            }
            if (!Schema::hasColumn('leads', 'timezone')) {
                $table->string('timezone')->nullable()->after('preferred_contact');
            }
            if (!Schema::hasColumn('leads', 'mc_number')) {
                $table->string('mc_number')->nullable()->after('company_name');
            }
            if (!Schema::hasColumn('leads', 'dot_number')) {
                $table->string('dot_number')->nullable()->after('mc_number');
            }
            if (!Schema::hasColumn('leads', 'years_in_business')) {
                $table->unsignedSmallInteger('years_in_business')->nullable()->after('dot_number');
            }
            if (!Schema::hasColumn('leads', 'website')) {
                $table->string('website')->nullable()->after('years_in_business');
            }
            if (!Schema::hasColumn('leads', 'equipment')) {
                $table->json('equipment')->nullable()->after('website');
            }
            if (!Schema::hasColumn('leads', 'trucks_count')) {
                $table->unsignedSmallInteger('trucks_count')->nullable()->after('equipment');
            }
            if (!Schema::hasColumn('leads', 'currently_running')) {
                $table->boolean('currently_running')->default(false)->after('trucks_count');
            }
            if (!Schema::hasColumn('leads', 'working_with_dispatcher')) {
                $table->boolean('working_with_dispatcher')->default(false)->after('currently_running');
            }
            if (!Schema::hasColumn('leads', 'preferred_lanes')) {
                $table->json('preferred_lanes')->nullable()->after('working_with_dispatcher');
            }
            if (!Schema::hasColumn('leads', 'preferred_load_types')) {
                $table->json('preferred_load_types')->nullable()->after('preferred_lanes');
            }
            if (!Schema::hasColumn('leads', 'min_rate_per_mile')) {
                $table->decimal('min_rate_per_mile', 8, 2)->nullable()->after('preferred_load_types');
            }
            if (!Schema::hasColumn('leads', 'max_deadhead_miles')) {
                $table->unsignedSmallInteger('max_deadhead_miles')->nullable()->after('min_rate_per_mile');
            }
            if (!Schema::hasColumn('leads', 'runs_weekends')) {
                $table->boolean('runs_weekends')->default(false)->after('max_deadhead_miles');
            }
            if (!Schema::hasColumn('leads', 'home_time')) {
                $table->text('home_time')->nullable()->after('runs_weekends');
            }
            if (!Schema::hasColumn('leads', 'expectation_rate')) {
                $table->decimal('expectation_rate', 8, 2)->nullable()->after('home_time');
            }
            if (!Schema::hasColumn('leads', 'current_weekly_gross')) {
                $table->decimal('current_weekly_gross', 10, 2)->nullable()->after('expectation_rate');
            }
            if (!Schema::hasColumn('leads', 'objections')) {
                $table->text('objections')->nullable()->after('current_weekly_gross');
            }
            if (!Schema::hasColumn('leads', 'last_contact_at')) {
                $table->timestamp('last_contact_at')->nullable()->after('updated_at');
            }
            if (!Schema::hasColumn('leads', 'next_follow_up_at')) {
                $table->timestamp('next_follow_up_at')->nullable()->after('last_contact_at');
            }
            if (!Schema::hasColumn('leads', 'pipeline_stage_id')) {
                $table->foreignId('pipeline_stage_id')->nullable()->after('status')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('leads', 'lead_source_id')) {
                $table->foreignId('lead_source_id')->nullable()->after('pipeline_stage_id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('leads', 'owner_id')) {
                $table->foreignId('owner_id')->nullable()->after('lead_source_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('leads', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->after('owner_id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $drops = [
                'whatsapp',
                'preferred_contact',
                'timezone',
                'mc_number',
                'dot_number',
                'years_in_business',
                'website',
                'equipment',
                'trucks_count',
                'currently_running',
                'working_with_dispatcher',
                'preferred_lanes',
                'preferred_load_types',
                'min_rate_per_mile',
                'max_deadhead_miles',
                'runs_weekends',
                'home_time',
                'expectation_rate',
                'current_weekly_gross',
                'objections',
                'last_contact_at',
                'next_follow_up_at',
                'pipeline_stage_id',
                'lead_source_id',
                'owner_id',
            ];
            foreach ($drops as $column) {
                if (Schema::hasColumn('leads', $column)) {
                    $table->dropColumn($column);
                }
            }
            if (Schema::hasColumn('leads', 'assigned_to')) {
                $table->dropForeign(['assigned_to']);
            }
        });
    }
};
