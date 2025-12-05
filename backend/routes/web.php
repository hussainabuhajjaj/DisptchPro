<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadController;
use App\Filament\Pages\TransportBoard;
use App\Events\TmsMapUpdated;
use App\Models\Load;
use App\Models\LoadStop;
use App\Models\CheckCall;
use App\Http\Controllers\DocumentGenerationController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/lead', [LeadController::class, 'create']);
Route::post('/lead', [LeadController::class, 'store']);

// JSON map data for TMS (admin authenticated)
Route::middleware(['web', 'auth'])->get('/admin/tms-map-data', function () {
    return response()->json([
        'loads' => TransportBoard::mapData(),
    ]);
})->name('admin.tms-map-data');

// Trigger broadcast of current map data (for manual/push updates)
Route::middleware(['web', 'auth'])->post('/admin/tms-map-broadcast', function () {
    broadcast(new TmsMapUpdated());
    return response()->json(['ok' => true]);
})->name('admin.tms-map-broadcast');

// Add a stop quickly from the map (fuel/service/lodging/etc.)
Route::middleware(['web', 'auth'])->post('/admin/tms-add-stop', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'load_id' => ['required', 'exists:loads,id'],
        'type' => ['required', 'string', 'max:32'],
        'name' => ['nullable', 'string', 'max:255'],
        'city' => ['nullable', 'string', 'max:255'],
        'state' => ['nullable', 'string', 'max:255'],
        'lat' => ['required', 'numeric'],
        'lng' => ['required', 'numeric'],
    ]);

    $load = Load::findOrFail($data['load_id']);
    $nextSequence = (int) LoadStop::where('load_id', $load->id)->max('sequence') + 1;

    $stop = LoadStop::create([
        'load_id' => $load->id,
        'sequence' => $nextSequence,
        'type' => $data['type'],
        'facility_name' => $data['name'] ?? ucfirst($data['type']),
        'city' => $data['city'],
        'state' => $data['state'],
        'lat' => $data['lat'],
        'lng' => $data['lng'],
    ]);

    return response()->json([
        'ok' => true,
        'stop' => $stop,
    ]);
})->name('admin.tms-add-stop');

// Quick assign/unassign dispatcher
Route::middleware(['web', 'auth'])->post('/admin/tms-assign-dispatcher', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'load_id' => ['required', 'exists:loads,id'],
        'dispatcher_id' => ['nullable', 'exists:users,id'],
    ]);
    $load = Load::findOrFail($data['load_id']);
    $load->dispatcher_id = $data['dispatcher_id'];
    $load->saveQuietly();
    broadcast(new TmsMapUpdated());
    return response()->json(['ok' => true, 'dispatcher_id' => $load->dispatcher_id]);
})->name('admin.tms-assign-dispatcher');

// Quick check-call logger
Route::middleware(['web', 'auth'])->post('/admin/tms-check-call', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'load_id' => ['required', 'exists:loads,id'],
        'status' => ['required', 'string', 'max:64'],
        'note' => ['nullable', 'string'],
    ]);
    $load = Load::findOrFail($data['load_id']);
    $call = CheckCall::create([
        'load_id' => $load->id,
        'user_id' => Auth::id(),
        'status' => $data['status'],
        'note' => $data['note'],
        'reported_at' => now(),
    ]);

    // Simple status transition (mirror relation manager)
    $order = ['draft', 'posted', 'assigned', 'in_transit', 'delivered', 'completed'];
    $map = [
        'dispatched' => 'posted',
        'en_route' => 'in_transit',
        'arrived_pickup' => 'in_transit',
        'loaded' => 'in_transit',
        'arrived_delivery' => 'delivered',
        'unloaded' => 'delivered',
    ];
    $newStatus = $map[$call->status] ?? $load->status;
    $currentIndex = array_search($load->status, $order);
    $newIndex = array_search($newStatus, $order);
    if (in_array($call->status, ['arrived_pickup', 'loaded']) && is_null($load->pickup_actual_at)) {
        $load->pickup_actual_at = now();
    }
    if (in_array($call->status, ['arrived_delivery', 'unloaded']) && is_null($load->delivery_actual_at)) {
        $load->delivery_actual_at = now();
    }
    if ($newIndex !== false && $currentIndex !== false && $newIndex >= $currentIndex && $newStatus !== $load->status) {
        $load->status = $newStatus;
    }
    $load->saveQuietly();

    broadcast(new TmsMapUpdated());

    return response()->json(['ok' => true, 'call_id' => $call->id]);
})->name('admin.tms-check-call');

// Generate PDF for loads
Route::middleware(['web', 'auth'])->get('/admin/documents/loads/{load}/pdf', [DocumentGenerationController::class, 'loadPdf'])->name('admin.documents.loads.pdf');
Route::middleware(['web', 'auth'])->get('/admin/documents/invoices/{invoice}/pdf', [DocumentGenerationController::class, 'invoicePdf'])->name('admin.documents.invoices.pdf');
Route::middleware(['web', 'auth'])->get('/admin/documents/settlements/{settlement}/pdf', [DocumentGenerationController::class, 'settlementPdf'])->name('admin.documents.settlements.pdf');

// Simple health check (unauthenticated)
Route::get('/health', function () {
    return response()->json(['ok' => true, 'status' => 'up']);
});
