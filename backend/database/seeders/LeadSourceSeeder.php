<?php

namespace Database\Seeders;

use App\Models\LeadSource;
use Illuminate\Database\Seeder;

class LeadSourceSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            'Website',
            'Social Media',
            'Referral',
            'Paid Ads',
            'Email Campaign',
            'Cold Call',
        ];

        foreach ($sources as $name) {
            LeadSource::firstOrCreate(['name' => $name]);
        }
    }
}
