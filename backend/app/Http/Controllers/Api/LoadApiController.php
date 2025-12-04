<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoadResource;
use App\Models\Load;
use Illuminate\Http\Request;

class LoadApiController extends Controller
{
    public function index(Request $request)
    {
        $q = Load::query()->with(['client', 'carrier', 'driver', 'stops']);
        if ($status = $request->query('status')) {
            $q->where('status', $status);
        }
        if ($from = $request->query('date_from')) {
            $q->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->query('date_to')) {
            $q->whereDate('created_at', '<=', $to);
        }

        return LoadResource::collection($q->paginate(50));
    }

    public function show(Load $load)
    {
        $load->load(['client', 'carrier', 'driver', 'stops']);
        return new LoadResource($load);
    }

    public function updateStatus(Request $request, Load $load)
    {
        $request->validate([
            'status' => 'required|in:draft,posted,assigned,in_transit,delivered,completed,cancelled',
        ]);
        $load->update(['status' => $request->status]);
        return new LoadResource($load);
    }
}
