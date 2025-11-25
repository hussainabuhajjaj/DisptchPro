<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary()
    {
        // Replace with real metrics later
            return response()->json([
            'loadsThisMonth' => 18,
            'avgRatePerMile' => 2.71,
            'nextSettlement' => [
                'amount' => 14200,
                'date' => now()->addDays(5)->toDateString(),
                'issues' => ['Waiting on POD for LD-1197'],
            ],
        ]);
    }

    public function loads(Request $request)
    {
        $filterEquipment = $request->query('equipment');
        $filterMinRpm = $request->query('min_rpm');
        $perPage = max((int) $request->query('per_page', 10), 1);
        $page = max((int) $request->query('page', 1), 1);

        $loads = [
            ['id' => 'LB-2101', 'lane' => 'DFW → MCI', 'equipment' => 'Dry Van', 'rpm' => 2.30, 'pickup' => now()->addDay()->toDateString()],
            ['id' => 'LB-2102', 'lane' => 'AUS → DEN', 'equipment' => 'Reefer', 'rpm' => 2.95, 'pickup' => now()->addDays(2)->toDateString()],
            ['id' => 'LB-2103', 'lane' => 'OKC → MEM', 'equipment' => 'Flatbed', 'rpm' => 2.45, 'pickup' => now()->addDay()->toDateString()],
            ['id' => 'LB-2104', 'lane' => 'HOU → CHI', 'equipment' => 'Dry Van', 'rpm' => 2.65, 'pickup' => now()->addDays(3)->toDateString()],
        ];

        $filtered = collect($loads)
            ->when($filterEquipment, fn($c) => $c->where('equipment', $filterEquipment))
            ->when($filterMinRpm, fn($c) => $c->where('rpm', '>=', (float) $filterMinRpm))
            ->values();

        $total = $filtered->count();
        $paginated = $filtered->forPage($page, $perPage)->values()->all();

        return response()->json([
            'data' => $paginated,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function requestLoad(Request $request, string $id)
    {
        // Record request; for now, just return success.
        return response()->json(['success' => true, 'requested' => $id]);
    }
}
