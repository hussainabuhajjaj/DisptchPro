<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carriers', function (Blueprint $table): void {
            if (!Schema::hasColumn('carriers', 'usdot_number')) {
                $table->string('usdot_number', 32)->nullable()->after('DOT_number');
                $table->index('usdot_number');
            }
            if (!Schema::hasColumn('carriers', 'safer_snapshot')) {
                $table->json('safer_snapshot')->nullable()->after('safer_profile_url');
            }
            if (!Schema::hasColumn('carriers', 'coi_expires_at')) {
                $table->dateTime('coi_expires_at')->nullable()->after('coi_document_path')->index();
            }
            if (!Schema::hasColumn('carriers', 'coi_received_at')) {
                $table->dateTime('coi_received_at')->nullable()->after('coi_expires_at');
            }
            if (!Schema::hasColumn('carriers', 'onboarding_checklist')) {
                $table->json('onboarding_checklist')->nullable()->after('coi_received_at');
            }
            if (!Schema::hasColumn('carriers', 'onboarding_verified_at')) {
                $table->dateTime('onboarding_verified_at')->nullable()->after('onboarding_checklist');
            }
            if (!Schema::hasColumn('carriers', 'onboarding_verification_status')) {
                $table->string('onboarding_verification_status', 50)->nullable()->after('onboarding_verified_at')->index();
            }
        });

        Schema::table('drivers', function (Blueprint $table): void {
            if (!Schema::hasColumn('drivers', 'eld_device_id')) {
                $table->string('eld_device_id', 100)->nullable()->after('api_token_expires_at');
            }
            if (!Schema::hasColumn('drivers', 'hos_provider')) {
                $table->string('hos_provider', 100)->nullable()->after('eld_device_id');
            }
            if (!Schema::hasColumn('drivers', 'hos_last_import_at')) {
                $table->dateTime('hos_last_import_at')->nullable()->after('hos_provider');
            }
            if (!Schema::hasColumn('drivers', 'hazmat_endorsement')) {
                $table->boolean('hazmat_endorsement')->default(false)->after('endorsements');
            }
            if (!Schema::hasColumn('drivers', 'tracking_opt_in')) {
                $table->boolean('tracking_opt_in')->default(true)->after('availability')->index();
            }
        });

        Schema::table('loads', function (Blueprint $table): void {
            if (!Schema::hasColumn('loads', 'lifecycle_status')) {
                $table->string('lifecycle_status', 50)->default('draft')->after('status')->index();
            }
            if (!Schema::hasColumn('loads', 'hazmat_flag')) {
                $table->boolean('hazmat_flag')->default(false)->after('weight');
            }
            if (!Schema::hasColumn('loads', 'hazmat_details')) {
                $table->text('hazmat_details')->nullable()->after('hazmat_flag');
            }
            if (!Schema::hasColumn('loads', 'weight_axle_limits')) {
                $table->json('weight_axle_limits')->nullable()->after('weight');
            }
            if (!Schema::hasColumn('loads', 'rate_confirmed_at')) {
                $table->dateTime('rate_confirmed_at')->nullable()->after('rate_con_path');
            }
        });

        Schema::table('load_stops', function (Blueprint $table): void {
            if (!Schema::hasColumn('load_stops', 'timezone')) {
                $table->string('timezone', 64)->nullable()->after('appointment_time');
            }
            if (!Schema::hasColumn('load_stops', 'window_start')) {
                $table->dateTime('window_start')->nullable()->after('timezone');
            }
            if (!Schema::hasColumn('load_stops', 'window_end')) {
                $table->dateTime('window_end')->nullable()->after('window_start');
            }
            if (!Schema::hasColumn('load_stops', 'geofence_radius_m')) {
                $table->decimal('geofence_radius_m', 8, 2)->nullable()->after('window_end');
            }
            if (!Schema::hasColumn('load_stops', 'is_appointment_required')) {
                $table->boolean('is_appointment_required')->default(false)->after('geofence_radius_m');
            }
            $table->index(['load_id', 'sequence']);
        });

        Schema::table('check_calls', function (Blueprint $table): void {
            if (!Schema::hasColumn('check_calls', 'event_code')) {
                $table->string('event_code', 32)->nullable()->after('status');
            }
            if (!Schema::hasColumn('check_calls', 'lat')) {
                $table->decimal('lat', 10, 7)->nullable()->after('event_code');
            }
            if (!Schema::hasColumn('check_calls', 'lng')) {
                $table->decimal('lng', 10, 7)->nullable()->after('lat');
            }
            if (!Schema::hasColumn('check_calls', 'location_source')) {
                $table->string('location_source', 50)->nullable()->after('lng');
            }
            if (!Schema::hasColumn('check_calls', 'recorded_at')) {
                $table->dateTime('recorded_at')->nullable()->after('location_source');
            }
            $table->index(['event_code']);
        });

        Schema::table('documents', function (Blueprint $table): void {
            if (!Schema::hasColumn('documents', 'category')) {
                $table->string('category', 64)->nullable()->after('type')->index();
            }
            if (!Schema::hasColumn('documents', 'metadata')) {
                $table->json('metadata')->nullable()->after('size');
            }
        });
    }

    public function down(): void
    {
        Schema::table('carriers', function (Blueprint $table): void {
            if (Schema::hasColumn('carriers', 'usdot_number')) {
                $table->dropIndex(['usdot_number']);
                $table->dropColumn(['usdot_number']);
            }
            if (Schema::hasColumn('carriers', 'safer_snapshot')) {
                $table->dropColumn('safer_snapshot');
            }
            if (Schema::hasColumn('carriers', 'coi_expires_at')) {
                $table->dropIndex(['coi_expires_at']);
                $table->dropColumn(['coi_expires_at', 'coi_received_at']);
            }
            if (Schema::hasColumn('carriers', 'onboarding_checklist')) {
                $table->dropColumn(['onboarding_checklist', 'onboarding_verified_at', 'onboarding_verification_status']);
            }
        });

        Schema::table('drivers', function (Blueprint $table): void {
            if (Schema::hasColumn('drivers', 'hazmat_endorsement')) {
                $table->dropColumn('hazmat_endorsement');
            }
            if (Schema::hasColumn('drivers', 'tracking_opt_in')) {
                $table->dropIndex(['tracking_opt_in']);
                $table->dropColumn('tracking_opt_in');
            }
            if (Schema::hasColumn('drivers', 'eld_device_id')) {
                $table->dropColumn(['eld_device_id', 'hos_provider', 'hos_last_import_at']);
            }
        });

        Schema::table('loads', function (Blueprint $table): void {
            if (Schema::hasColumn('loads', 'lifecycle_status')) {
                $table->dropIndex(['lifecycle_status']);
                $table->dropColumn('lifecycle_status');
            }
            if (Schema::hasColumn('loads', 'hazmat_flag')) {
                $table->dropColumn(['hazmat_flag', 'hazmat_details']);
            }
            if (Schema::hasColumn('loads', 'weight_axle_limits')) {
                $table->dropColumn('weight_axle_limits');
            }
            if (Schema::hasColumn('loads', 'rate_confirmed_at')) {
                $table->dropColumn('rate_confirmed_at');
            }
        });

        Schema::table('load_stops', function (Blueprint $table): void {
            if (Schema::hasColumn('load_stops', 'timezone')) {
                $table->dropColumn(['timezone', 'window_start', 'window_end', 'geofence_radius_m', 'is_appointment_required']);
            }
            $table->dropIndex(['load_id', 'sequence']);
        });

        Schema::table('check_calls', function (Blueprint $table): void {
            if (Schema::hasColumn('check_calls', 'event_code')) {
                $table->dropIndex(['event_code']);
                $table->dropColumn(['event_code', 'lat', 'lng', 'location_source', 'recorded_at']);
            }
        });

        Schema::table('documents', function (Blueprint $table): void {
            if (Schema::hasColumn('documents', 'category')) {
                $table->dropIndex(['category']);
                $table->dropColumn('category');
            }
            if (Schema::hasColumn('documents', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });
    }
};
