<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\PipelineStage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LeadKanbanController extends Controller
{
    public function index(): JsonResponse
    {
        // Guard against missing CRM tables in fresh installs
        if (!Schema::hasTable('pipeline_stages') || !Schema::hasTable('leads')) {
            return response()->json(['columns' => []]);
        }

        try {
            $stageMeta = $this->stageMeta();
            $stages = PipelineStage::orderBy('position')->get();
            if ($stages->isEmpty()) {
                $stages = $this->bootstrapStages();
            }
            $leads = Lead::query()
                ->with(['owner', 'assignee', 'tags', 'source'])
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('pipeline_stage_id');

            $columns = $stages->map(function (PipelineStage $stage, $index) use ($leads, $stageMeta) {
                $slug = Str::slug($stage->name);
                $meta = $stageMeta[$slug] ?? [];
                $limit = $meta['wip_limit'] ?? null;
                $color = $meta['color'] ?? $this->palette($index);

                $cards = ($leads[$stage->id] ?? collect())->map(function (Lead $lead) {
                    $summary = $lead->notes ?? $lead->freight_details ?? null;
                    $tags = $lead->tags->pluck('name')->all();
                    return [
                        'id' => $lead->id,
                        'name' => $lead->name,
                        'company' => $lead->company_name,
                        'status' => $lead->status,
                        'owner' => $lead->owner?->name,
                        'assignee' => $lead->assignee?->name,
                        'created_at' => $lead->created_at?->toDateTimeString(),
                        'summary' => $summary ? Str::limit($summary, 160) : null,
                        'phone' => $lead->phone,
                        'email' => $lead->email,
                        'next_follow_up_at' => $lead->next_follow_up_at?->toDateTimeString(),
                        'tags' => $tags,
                        'source' => $lead->source?->name,
                    ];
                })->values();

                return [
                    'id' => $stage->id,
                    'label' => $stage->name,
                    'slug' => $slug,
                    'color' => $color,
                    'wip_limit' => $limit,
                    'count' => $cards->count(),
                    'cards' => $cards,
                ];
            })->values();

            return response()->json(['columns' => $columns]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['error' => 'Unable to load Kanban. Check server logs.'], 500);
        }
    }

    public function move(Request $request, Lead $lead): JsonResponse
    {
        $data = $request->validate([
            'pipeline_stage_id' => ['required', 'exists:pipeline_stages,id'],
        ]);

        $stage = PipelineStage::findOrFail($data['pipeline_stage_id']);
        $stageSlug = Str::slug($stage->name);
        $stageMeta = $this->stageMeta();
        $limit = $stageMeta[$stageSlug]['wip_limit'] ?? null;

        if ($limit) {
            $count = Lead::where('pipeline_stage_id', $stage->id)->count();
            if ($count >= $limit) {
                throw ValidationException::withMessages([
                    'pipeline_stage_id' => "WIP limit reached for {$stage->name}.",
                ]);
            }
        }

        // Simple rule: agreement/onboarding requires contact info
        if (in_array($stageSlug, ['agreement-sent', 'agreement', 'onboarding']) && !$lead->email && !$lead->phone) {
            throw ValidationException::withMessages([
                'pipeline_stage_id' => 'Add an email or phone before moving to Agreement/Onboarding.',
            ]);
        }

        $lead->update([
            'pipeline_stage_id' => $data['pipeline_stage_id'],
            'status' => $lead->status ?? 'contacted',
        ]);

        return response()->json(['ok' => true]);
    }

    protected function palette(int $index): string
    {
        $colors = ['#60a5fa', '#34d399', '#f59e0b', '#6366f1', '#f87171', '#06b6d4', '#f472b6'];
        return $colors[$index % count($colors)];
    }

    protected function stageMeta(): array
    {
        return [
            'new-lead' => ['wip_limit' => 20, 'color' => '#60a5fa'],
            'contacted' => ['wip_limit' => 20, 'color' => '#34d399'],
            'qualified' => ['wip_limit' => 15, 'color' => '#10b981'],
            'agreement-sent' => ['wip_limit' => 10, 'color' => '#f59e0b'],
            'onboarding' => ['wip_limit' => 10, 'color' => '#6366f1'],
            'active-carrier' => ['wip_limit' => null, 'color' => '#16a34a'],
            'inactive-lost' => ['wip_limit' => null, 'color' => '#94a3b8'],
        ];
    }

    /**
     * Bootstrap a default pipeline if none exist (helps when seeders haven't run).
     */
    protected function bootstrapStages()
    {
        $defaults = [
            'New Lead',
            'Contacted',
            'Qualified',
            'Agreement Sent',
            'Onboarding',
            'Active Carrier',
            'Inactive / Lost',
        ];

        foreach ($defaults as $i => $name) {
            PipelineStage::firstOrCreate(
                ['name' => $name],
                ['position' => $i + 1, 'is_default' => $i === 0]
            );
        }

        return PipelineStage::orderBy('position')->get();
    }
}
