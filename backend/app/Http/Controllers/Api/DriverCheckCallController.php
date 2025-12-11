<?php

namespace App\Http\Controllers\Api;

use App\Events\TmsMapUpdated;
use App\Http\Controllers\Controller;
use App\Models\CheckCall;
use App\Models\Document;
use App\Models\Load;
use App\Models\LoadStop;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use App\Notifications\SlaAlertNotification;
use App\Models\User;

class DriverCheckCallController extends Controller
{
    public function store(Request $request)
    {
        $driver = $this->authenticateDriver($request);
        if (!$driver) {
            return response()->json(['message' => 'Driver token required'], 401);
        }

        $data = $request->validate([
            'load_number' => 'required_without:load_id|string',
            'load_id' => 'required_without:load_number|integer|exists:loads,id',
            'stop_id' => 'nullable|integer|exists:load_stops,id',
            'status' => 'required|string|max:191',
            'note' => 'nullable|string',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ]);

        $load = $data['load_id'] ?? null
            ? Load::find($data['load_id'])
            : Load::where('load_number', $data['load_number'])->first();

        if (!$load) {
            return response()->json(['message' => 'Load not found'], 404);
        }

        if ($load->driver_id && $driver && $load->driver_id !== $driver->id) {
            return response()->json(['message' => 'Driver is not assigned to this load'], 403);
        }

        if (!empty($data['stop_id'])) {
            $stop = LoadStop::find($data['stop_id']);
            if (!$stop || $stop->load_id !== $load->id) {
                return response()->json(['message' => 'Stop does not belong to this load'], 422);
            }
        }

        $checkCall = CheckCall::create([
            'load_id' => $load->id,
            'user_id' => null,
            'status' => $data['status'],
            'note' => $data['note'] ?? null,
            'reported_at' => now(),
        ]);

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $path = $file->store('documents/driver', 'public');

            Document::create([
                'documentable_type' => !empty($stop) ? LoadStop::class : Load::class,
                'documentable_id' => !empty($stop) ? $stop->id : $load->id,
                'type' => !empty($stop) ? 'stop:pod' : 'load:driver-doc',
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $driver?->id,
                'uploaded_at' => now(),
            ]);
        }

        // Broadcast map update
        try {
            event(new TmsMapUpdated('check_call', $load->id));
        } catch (\Throwable $e) {
            // ignore broadcast issues
        }

        // Alert if status indicates issue
        $watch = ['issue', 'delayed', 'delay', 'late'];
        $status = strtolower($data['status'] ?? '');
        if (collect($watch)->contains(fn ($w) => str_contains($status, $w))) {
            $reason = "Driver check call marked '{$data['status']}'";
            $users = User::all();
            Notification::send($users, new SlaAlertNotification($load->load_number ?? '#', $reason, $load->status, $load->id));
        }

        return response()->json([
            'message' => 'Check call recorded',
            'id' => $checkCall->id,
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
                $q->whereNull('api_token_expires_at')->orWhere('api_token_expires_at', '>=', now());
            })
            ->first();
    }
}
