<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoadResource;
use App\Models\Driver;

class DriverApiController extends Controller
{
    public function loads(Driver $driver)
    {
        $loads = $driver->loads()->with(['client', 'carrier', 'stops'])->latest()->get();
        return LoadResource::collection($loads);
    }
}
