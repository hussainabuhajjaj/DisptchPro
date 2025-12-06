<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublicLeadRequest;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadSource;
use Illuminate\Http\JsonResponse;

class LeadCaptureController extends Controller
{
    public function store(PublicLeadRequest $request): JsonResponse
    {
        $payload = $request->validated();

        // Resolve or create source if name provided instead of id.
        if (empty($payload['lead_source_id']) && !empty($payload['lead_source'])) {
            $payload['lead_source_id'] = LeadSource::firstOrCreate(
                ['name' => $payload['lead_source']],
                ['description' => 'Created via public capture']
            )->id;
        }
        unset($payload['lead_source']);

        // Normalize array-able fields from CSV strings.
        if (isset($payload['equipment']) && is_string($payload['equipment'])) {
            $payload['equipment'] = array_values(array_filter(array_map('trim', explode(',', $payload['equipment']))));
        }
        if (isset($payload['preferred_lanes']) && is_string($payload['preferred_lanes'])) {
            $payload['preferred_lanes'] = array_values(array_filter(array_map('trim', explode(',', $payload['preferred_lanes']))));
        }
        if (isset($payload['preferred_load_types']) && is_string($payload['preferred_load_types'])) {
            $payload['preferred_load_types'] = array_values(array_filter(array_map('trim', explode(',', $payload['preferred_load_types']))));
        }

        $lead = Lead::create($payload);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'type' => 'note',
            'summary' => 'Lead captured from public form',
            'meta' => [
                'source' => $payload['lead_source'] ?? $payload['lead_source_id'] ?? 'public',
                'contact' => [
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                ],
            ],
            'happened_at' => now(),
        ]);

        return response()->json([
            'message' => 'Lead captured',
            'id' => $lead->id,
        ], 201);
    }
}
