<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CheckCall;
use App\Models\Driver;
use App\Models\Load;
use App\Models\LoadStop;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SlaAlertNotification;
use App\Http\Resources\Driver\DriverJobResource;

class DriverJobsController extends Controller
{
    public function index(Request $request)
    {
        $driver = $this->authenticateDriver($request);
        if (!$driver) {
            return response()->json(['message' => 'Driver token required'], 401);
        }

        $loads = Load::query()
            ->with(['stops' => fn ($q) => $q->orderBy('sequence')])
            ->where('driver_id', $driver->id)
            ->whereNotIn('status', ['delivered', 'completed', 'cancelled'])
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return response()->json([
            'loads' => DriverJobResource::collection($loads),
        ]);
    }

    public function updateStatus(Request $request, Load $load)
    {
        $driver = $this->authenticateDriver($request);
        if (!$driver) {
            return response()->json(['message' => 'Driver token required'], 401);
        }
        if ($load->driver_id !== $driver->id) {
            return response()->json(['message' => 'Driver not assigned to this load'], 403);
        }

        $data = $request->validate([
            'status' => 'required|string|max:191',
            'note' => 'nullable|string',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ]);

        $statusMap = [
            'accept' => 'assigned',
            'reject' => 'posted',
            'en_route' => 'in_transit',
            'on_site' => 'in_transit',
            'completed' => 'delivered',
        ];

        if (isset($statusMap[$data['status']])) {
            $load->update(['status' => $statusMap[$data['status']]]);
        }

        $checkStatus = $data['status'];
        CheckCall::create([
            'load_id' => $load->id,
            'user_id' => null,
            'status' => $checkStatus,
            'note' => $data['note'] ?? null,
            'reported_at' => now(),
        ]);

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $path = $file->store('documents/driver', 'public');
            Document::create([
                'documentable_type' => Load::class,
                'documentable_id' => $load->id,
                'type' => 'load:driver-doc',
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $driver->id,
                'uploaded_at' => now(),
            ]);
        }

        if (str_contains(strtolower($checkStatus), 'issue') || str_contains(strtolower($checkStatus), 'delay')) {
            Notification::send(\App\Models\User::all(), new SlaAlertNotification($load->load_number ?? '#', "Driver marked {$checkStatus}", $load->status, $load->id));
        }

        return response()->json(['message' => 'Status recorded']);
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
