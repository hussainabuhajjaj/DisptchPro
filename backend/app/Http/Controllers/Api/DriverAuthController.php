<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DriverAuthController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'driver_id' => 'required|integer|exists:drivers,id',
            'phone' => 'required|string',
        ]);

        $driver = Driver::find($data['driver_id']);
        if (!$driver || !$this->phoneMatches($driver->phone, $data['phone'])) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $driver->ensureFreshToken();

        return response()->json([
            'token' => $token,
            'expires_at' => $driver->api_token_expires_at,
        ]);
    }

    /**
     * Rotate API token using existing token (re-auth).
     */
    public function rotate(Request $request)
    {
        $driver = $this->authenticateDriver($request);
        if (!$driver) {
            return response()->json(['message' => 'Driver token required'], 401);
        }

        $token = $driver->ensureFreshToken();

        return response()->json([
            'token' => $token,
            'expires_at' => $driver->api_token_expires_at,
        ]);
    }

    protected function phoneMatches(?string $stored, string $input): bool
    {
        if (!$stored) {
            return false;
        }
        $s = preg_replace('/\D+/', '', $stored);
        $i = preg_replace('/\D+/', '', $input);
        return $s === $i;
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
