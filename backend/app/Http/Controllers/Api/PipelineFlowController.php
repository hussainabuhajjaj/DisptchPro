<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PipelineFlowRequest;
use App\Models\PipelineStage;
use App\Models\PipelineTransition;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PipelineFlowController extends Controller
{
    public function show(): JsonResponse
    {
        $stages = PipelineStage::orderBy('position')->get();
        $transitions = PipelineTransition::with(['fromStage', 'toStage'])->get();

        return response()->json([
            'nodes' => $stages->map(function (PipelineStage $stage) {
                return [
                    'id' => $stage->id,
                    'label' => $stage->name,
                    'position' => $stage->position,
                    'x' => $stage->position_x,
                    'y' => $stage->position_y,
                ];
            })->values(),
            'edges' => $transitions->map(function (PipelineTransition $transition) {
                return [
                    'id' => $transition->id,
                    'from' => $transition->from_stage_id,
                    'to' => $transition->to_stage_id,
                    'label' => $transition->label,
                    'actions' => $transition->actions,
                ];
            })->values(),
        ]);
    }

    public function store(PipelineFlowRequest $request): JsonResponse
    {
        $data = $request->validated();
        $prune = $data['prune_missing'] ?? false;

        DB::transaction(function () use ($data, $prune) {
            // Update stage positions and ordering.
            foreach ($data['nodes'] as $index => $node) {
                PipelineStage::whereKey($node['id'])->update([
                    'position' => $node['position'] ?? $index,
                    'position_x' => $node['x'] ?? null,
                    'position_y' => $node['y'] ?? null,
                ]);
            }

            $keptIds = [];
            if (!empty($data['edges'])) {
                foreach ($data['edges'] as $edge) {
                    $transition = PipelineTransition::updateOrCreate(
                        ['id' => $edge['id'] ?? null],
                        [
                            'from_stage_id' => $edge['from'],
                            'to_stage_id' => $edge['to'],
                            'label' => $edge['label'] ?? null,
                            'actions' => $edge['actions'] ?? null,
                        ]
                    );
                    $keptIds[] = $transition->id;
                }
            }

            if ($prune) {
                PipelineTransition::whereNotIn('id', $keptIds)->delete();
            }
        });

        return response()->json(['status' => 'saved']);
    }
}
