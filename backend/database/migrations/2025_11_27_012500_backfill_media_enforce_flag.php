<?php

use App\Settings\MediaSettings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!class_exists(MediaSettings::class)) {
            return;
        }

        $defaults = MediaSettings::defaults();

        $existing = DB::table('settings')
            ->where('group', 'media')
            ->where('name', 'media')
            ->first();

        if ($existing) {
            $payload = json_decode($existing->payload, true) ?? [];
            $merged = array_merge($defaults, $payload);
            DB::table('settings')
                ->where('id', $existing->id)
                ->update([
                    'payload' => json_encode($merged),
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('settings')->insert([
                'group' => 'media',
                'name' => 'media',
                'payload' => json_encode($defaults),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // no-op
    }
};
