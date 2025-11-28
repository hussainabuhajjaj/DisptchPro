<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CarrierProfileRequest;
use App\Models\CarrierProfile;

class CarrierProfileController extends Controller
{
    public function store(CarrierProfileRequest $request)
    {
        $validated = $request->validated();

        $profile = CarrierProfile::create([
            'user_id' => optional($request->user())->id,
            'carrier_info' => $validated['carrierInfo'],
            'equipment_info' => $validated['equipmentInfo'] ?? null,
            'operation_info' => $validated['operationInfo'] ?? null,
            'factoring_info' => $validated['factoringInfo'] ?? null,
            'insurance_info' => $validated['insuranceInfo'] ?? null,
            'status' => 'submitted',
        ]);

        return response()->json([
            'id' => $profile->id,
            'status' => $profile->status,
            'createdAt' => $profile->created_at,
        ], 201);
    }
}
