<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;

class DriverPortalController extends Controller
{
    public function showLogin()
    {
        return view('driver.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'driver_id' => ['required', 'exists:drivers,id'],
            'phone' => ['required', 'string'],
        ]);

        $driver = Driver::find($data['driver_id']);
        if (!$driver || !$this->phoneMatches($driver->phone, $data['phone'])) {
            return back()->withErrors(['phone' => 'Invalid credentials'])->withInput();
        }

        $token = $driver->ensureFreshToken();

        $request->session()->put([
            'driver_id' => $driver->id,
            'driver_token' => $token,
        ]);

        return redirect()->route('driver.dashboard');
    }

    public function dashboard(Request $request)
    {
        /** @var Driver $driver */
        $driver = $request->attributes->get('driver') ?? Driver::find($request->session()->get('driver_id'));
        $token = $request->session()->get('driver_token');

        return view('driver.dashboard', [
            'driver' => $driver,
            'token' => $token,
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
}
