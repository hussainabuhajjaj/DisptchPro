<?php

namespace App\Http\Middleware;

use App\Models\Driver;
use Closure;
use Illuminate\Http\Request;

class DriverSession
{
    public function handle(Request $request, Closure $next)
    {
        $driverId = $request->session()->get('driver_id');
        $token = $request->session()->get('driver_token');

        if (!$driverId || !$token) {
            return redirect()->route('driver.login.form');
        }

        $driver = Driver::find($driverId);
        if (!$driver || !$driver->hasValidToken($token)) {
            $request->session()->forget(['driver_id', 'driver_token']);
            return redirect()->route('driver.login.form');
        }

        // Attach driver to request for convenience
        $request->attributes->set('driver', $driver);

        return $next($request);
    }
}
