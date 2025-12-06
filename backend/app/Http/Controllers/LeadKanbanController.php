<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\PipelineStage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadKanbanController extends Controller
{
    public function index(): JsonResponse
    {
        $stages = PipelineStage::orderBy('position')->get();
        $leads = Lead::query()
            ->with(['owner', 'assignee'])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('pipeline_stage_id');

        $columns = $stages->map(function (PipelineStage $stage) use ($leads) {
            $cards = ($leads[$stage->id] ?? collect())->map(function (Lead $lead) {
                return [
                    'id' => $lead->id,
                    'name' => $lead->name,
                    'company' => $lead->company_name,
                    'status' => $lead->status,
                    'owner' => $lead->owner?->name,
                    'assignee' => $lead->assignee?->name,
                    'created_at' => $lead->created_at?->toDateTimeString(),
                ];
            })->values();

            return [
                'id' => $stage->id,
                'label' => $stage->name,
                'cards' => $cards,
            ];
        })->values();

        return response()->json(['columns' => $columns]);
    }

    public function move(Request $request, Lead $lead): JsonResponse
    {
        $data = $request->validate([
            'pipeline_stage_id' => ['required', 'exists:pipeline_stages,id'],
        ]);

        $lead->update([
            'pipeline_stage_id' => $data['pipeline_stage_id'],
            'status' => $lead->status ?? 'contacted',
        ]);

        return response()->json(['ok' => true]);
    }
}
