<?php

namespace Database\Seeders;

use App\Models\PipelineStage;
use Illuminate\Database\Seeder;

class PipelineStageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            'New Lead',
            'Contacted',
            'Qualified',
            'Agreement Sent',
            'Agreement Signed',
            'Onboarding',
            'Searching Loads',
            'First Load Booked',
            'Active Carrier',
            'Inactive / Lost',
        ];

        foreach ($stages as $index => $name) {
            PipelineStage::updateOrCreate(
                ['name' => $name],
                ['position' => $index, 'is_default' => $index === 0]
            );
        }
    }
}
