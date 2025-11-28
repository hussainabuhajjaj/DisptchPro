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

        DB::table('settings')->updateOrInsert(
            ['group' => 'media', 'name' => 'media'],
            [
                'payload' => json_encode($defaults),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('group', 'media')
            ->where('name', 'media')
            ->delete();
    }
};
