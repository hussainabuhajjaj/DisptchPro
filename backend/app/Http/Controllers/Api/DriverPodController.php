<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Load;
use App\Models\Pod;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class DriverPodController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $driver = $this->authenticateDriver($request);
        if (!$driver) {
            return response()->json(['message' => 'Driver token required'], 401);
        }

        $data = $request->validate([
            'load_id' => 'required|integer|exists:loads,id',
            'signer_name' => 'required|string|max:255',
            'signer_title' => 'nullable|string|max:255',
            'signed_at' => 'nullable|date',
            'photo' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:20480',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'accuracy_m' => 'nullable|numeric|min:0',
        ]);

        $load = Load::find($data['load_id']);
        if (!$load) {
            return response()->json(['message' => 'Load not found'], 404);
        }
        if ($load->driver_id && $load->driver_id !== $driver->id) {
            return response()->json(['message' => 'Driver not assigned to this load'], 403);
        }

        $path = $request->file('photo')->store('pods', 'public');

        $location = null;
        if (isset($data['lat'], $data['lng'])) {
            $location = [
                'lat' => (float) $data['lat'],
                'lng' => (float) $data['lng'],
                'accuracy_m' => $data['accuracy_m'] ?? null,
            ];
        }

        $pod = Pod::create([
            'load_id' => $load->id,
            'driver_id' => $driver->id,
            'signer_name' => $data['signer_name'],
            'signer_title' => $data['signer_title'] ?? null,
            'signed_at' => $data['signed_at'] ?? now(),
            'photo_path' => $path,
            'location' => $location,
        ]);

        // Optionally mark load as delivered if not already
        if (!in_array($load->status, ['delivered', 'completed'])) {
            $load->update(['status' => 'delivered']);
        }

        return response()->json([
            'message' => 'POD saved',
            'pod_id' => $pod->id,
            'photo_url' => Storage::disk('public')->url($path),
        ], 201);
    }

    protected function authenticateDriver(Request $request): ?Driver
    {
        $token = $request->header('X-Driver-Token');
        if (!$token) {
            return null;
        }

        return Driver::where('api_token', $token)
            ->where(function ($q) {
                $q->whereNull('api_token_expires_at')
                    ->orWhere('api_token_expires_at', '>=', now());
            })
            ->first();
    }
}
