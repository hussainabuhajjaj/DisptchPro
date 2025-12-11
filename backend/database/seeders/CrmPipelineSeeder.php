<?php

namespace Database\Seeders;

use App\Models\LeadSource;
use App\Models\LeadTag;
use App\Models\PipelineStage;
use App\Models\PipelineTransition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CrmPipelineSeeder extends Seeder
{
    public function run(): void
    {
        // Define pipeline stages in order
        $stages = [
            ['name' => 'New Lead', 'description' => 'Fresh inbound capture', 'is_default' => true],
            ['name' => 'Contacted', 'description' => 'Initial outreach done'],
            ['name' => 'Qualified', 'description' => 'Fit confirmed (authority, insurance, equipment)'],
            ['name' => 'Agreement Sent', 'description' => 'Dispatch agreement/packet sent'],
            ['name' => 'Onboarding', 'description' => 'Docs collected, ready to run'],
            ['name' => 'Active Carrier', 'description' => 'Running loads with us'],
            ['name' => 'Inactive / Lost', 'description' => 'Stopped responding or churned'],
        ];

        $stageIds = [];
        foreach ($stages as $index => $stage) {
            $record = PipelineStage::updateOrCreate(
                ['name' => $stage['name']],
                [
                    'description' => $stage['description'] ?? null,
                    'position' => $index + 1,
                    'is_default' => $stage['is_default'] ?? false,
                ],
            );
            $stageIds[Str::slug($stage['name'])] = $record->id;
        }

        // Define transitions (from -> to)
        $transitions = [
            ['from' => 'new-lead', 'to' => 'contacted', 'label' => 'Contacted'],
            ['from' => 'contacted', 'to' => 'qualified', 'label' => 'Qualified'],
            ['from' => 'qualified', 'to' => 'agreement-sent', 'label' => 'Send Agreement'],
            ['from' => 'agreement-sent', 'to' => 'onboarding', 'label' => 'Docs Received'],
            ['from' => 'onboarding', 'to' => 'active-carrier', 'label' => 'Ready to Run'],
            ['from' => 'active-carrier', 'to' => 'inactive-lost', 'label' => 'Mark Inactive'],
        ];

        foreach ($transitions as $transition) {
            $fromId = $stageIds[$transition['from']] ?? null;
            $toId = $stageIds[$transition['to']] ?? null;
            if (!$fromId || !$toId) {
                continue;
            }
            PipelineTransition::updateOrCreate(
                ['from_stage_id' => $fromId, 'to_stage_id' => $toId],
                [
                    'label' => $transition['label'] ?? null,
                    'actions' => $transition['actions'] ?? null,
                ]
            );
        }

        // Seed sources
        $sources = [
            'Website',
            'Social Media',
            'Referral',
            'Paid Ads',
            'Email Campaign',
            'Cold Call',
            'Conference / Expo',
        ];
        foreach ($sources as $name) {
            LeadSource::firstOrCreate(['name' => $name]);
        }

        // Seed lead tags (basic segments)
        $tags = [
            ['name' => 'Hot', 'color' => '#f97316'],
            ['name' => 'Warm', 'color' => '#f59e0b'],
            ['name' => 'Trial', 'color' => '#6366f1'],
            ['name' => 'Box Truck', 'color' => '#0ea5e9'],
            ['name' => 'Dry Van', 'color' => '#22c55e'],
            ['name' => 'Reefer', 'color' => '#14b8a6'],
            ['name' => 'Flatbed', 'color' => '#ef4444'],
        ];
        foreach ($tags as $tag) {
            LeadTag::updateOrCreate(['name' => $tag['name']], ['color' => $tag['color']]);
        }
    }
}
