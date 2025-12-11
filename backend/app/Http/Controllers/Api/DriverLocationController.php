<?php

namespace App\Http\Controllers\Api;

use App\Actions\Drivers\RecordLocationAction;
use App\Events\TmsMapUpdated;
use App\Http\Controllers\Controller;
use App\Jobs\PersistDriverLocationJob;
use App\Models\DriverApiToken;
use App\Models\Driver;
use App\Models\Load;
use App\Notifications\SlaAlertNotification;
use App\Services\Geo\RouteService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class DriverLocationController extends Controller
{
    public function __construct(
        protected RecordLocationAction $recordLocationAction,
        protected RouteService $routeService
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $driver = $this->authenticateDriver($request);
        if (!$driver) {
            return response()->json(['message' => 'Driver token required'], 401);
        }

        $data = $this->validatePayload($request);

        $load = $data['load_id'] ?? null
            ? Load::find($data['load_id'])
            : Load::where('load_number', $data['load_number'])->first();

        if (!$load) {
            return response()->json(['message' => 'Load not found'], 404);
        }

        if ($load->driver_id && $load->driver_id !== $driver->id) {
            return response()->json(['message' => 'Driver is not assigned to this load'], 403);
        }

        $plausibility = $this->checkPlausibility($load, $data);
        if ($plausibility['ignored'] ?? false) {
            return response()->json([
                'message' => 'Location ignored',
                'ignored' => true,
                'reason' => $plausibility['reason'],
            ], 202);
        }

        $useQueue = (bool) config('tracking.queue_locations', false);
        $result = null;

        try {
            if ($useQueue) {
                PersistDriverLocationJob::dispatch($driver->id, $load->id, $data);
            } else {
                $result = DB::transaction(function () use ($driver, $load, $data) {
                    return $this->recordLocationAction->execute($driver, $load, $data);
                });
            }
        } catch (\Throwable $e) {
            Log::error('Failed to record driver location', [
                'driver_id' => $driver->id,
                'load_id' => $load->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Failed to record location'], 500);
        }

        try {
            if ($this->isEtaLate($load)) {
                $reason = 'ETA is projected late based on current position';
                Notification::send(
                    \App\Models\User::all(),
                    new SlaAlertNotification($load->load_number ?? '#', $reason, $load->status, $load->id)
                );
            }
        } catch (\Throwable $e) {
            Log::warning('SLA alert notification failed', ['error' => $e->getMessage()]);
        }

        try {
            $payload = [
                'load_id' => $load->id,
                'driver_id' => $driver->id,
                'lat' => (float) $data['lat'],
                'lng' => (float) $data['lng'],
                'speed' => $data['speed'] ?? null,
                'heading' => $data['heading'] ?? null,
                'recorded_at' => isset($data['recorded_at'])
                    ? Carbon::parse($data['recorded_at'])->toIso8601String()
                    : Carbon::now()->toIso8601String(),
            ];
            event(new TmsMapUpdated('location', $load->id, $payload));
        } catch (\Throwable $e) {
            report($e);
        }

        $ignored = is_array($result) ? ($result['ignored'] ?? false) : false;
        $statusCode = $ignored ? 202 : ($useQueue ? 202 : 201);

        return response()->json([
            'message' => $ignored
                ? 'Location ignored'
                : ($useQueue ? 'Location accepted for processing' : 'Location recorded'),
            'id' => is_array($result) ? ($result['location_id'] ?? null) : null,
            'eta_minutes' => is_array($result) ? ($result['eta_minutes'] ?? $load->last_eta_minutes) : $load->last_eta_minutes,
            'ignored' => $ignored,
            'reason' => is_array($result) ? ($result['reason'] ?? null) : null,
        ], $statusCode);
    }

    protected function authenticateDriver(Request $request): ?Driver
    {
        $bearer = $request->bearerToken();
        if ($bearer) {
            $token = DriverApiToken::where('token_hash', hash('sha256', $bearer))
                ->whereNull('revoked_at')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', Carbon::now());
                })
                ->first();

            if ($token && $token->driver && $token->driver->tracking_opt_in) {
                $token->forceFill(['last_used_at' => Carbon::now()])->saveQuietly();
                return $token->driver;
            }
        }

        $legacy = $request->header('X-Driver-Token');
        if ($legacy) {
            return Driver::where('api_token', $legacy)
                ->where(function ($q) {
                    $q->whereNull('api_token_expires_at')
                        ->orWhere('api_token_expires_at', '>=', Carbon::now());
                })
                ->first();
        }

        return null;
    }

    protected function isEtaLate(Load $load): bool
    {
        if (!$load->last_eta_minutes) {
            return false;
        }

        $final = $load->stops()
            ->where('type', 'delivery')
            ->orderBy('sequence')
            ->get()
            ->last();

        if (!$final || !$final->date_from) {
            return false;
        }

        $etaArrival = Carbon::now()->addMinutes($load->last_eta_minutes);
        $scheduled = $final->date_from instanceof Carbon ? $final->date_from : Carbon::parse($final->date_from);

        return $etaArrival->greaterThan($scheduled);
    }

    protected function validatePayload(Request $request): array
    {
        $data = $request->validate([
            'load_number' => 'required_without:load_id|string',
            'load_id' => 'required_without:load_number|integer|exists:loads,id',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'speed' => 'nullable|numeric|min:0|max:200',
            'heading' => 'nullable|numeric|min:0|max:360',
            'accuracy_m' => 'nullable|numeric|min:0|max:5000',
            'source' => 'nullable|string|max:50',
            'track_id' => 'nullable|string|max:64',
            'recorded_at' => 'nullable|date',
        ]);

        $data['source'] = $data['source'] ?? 'gps';
        $data['recorded_at'] = $data['recorded_at'] ?? Carbon::now()->toIso8601String();

        return $data;
    }

    protected function checkPlausibility(Load $load, array $data): array
    {
        $speed = $data['speed'] ?? null;
        if ($speed !== null && $speed > 150) {
            return ['ignored' => true, 'reason' => 'Speed exceeds plausibility threshold'];
        }

        $recordedAt = isset($data['recorded_at']) ? Carbon::parse($data['recorded_at']) : Carbon::now();
        if ($load->last_lat && $load->last_lng && $load->last_location_at) {
            $seconds = max(1, $recordedAt->diffInSeconds($load->last_location_at));
            $jump = $this->routeService->isJumpUnrealistic(
                (float) $load->last_lat,
                (float) $load->last_lng,
                (float) $data['lat'],
                (float) $data['lng'],
                $seconds
            );
            if ($jump) {
                return ['ignored' => true, 'reason' => 'Jump distance too large for timeframe'];
            }
        }

        if (($data['accuracy_m'] ?? 0) > 5000) {
            return ['ignored' => true, 'reason' => 'GPS accuracy too low'];
        }

        return ['ignored' => false];
    }
}
