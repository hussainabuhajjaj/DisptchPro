<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\LeadController;
use App\Filament\Pages\TransportBoard;
use App\Events\TmsMapUpdated;
use App\Models\Load;
use App\Models\LoadStop;
use App\Models\CheckCall;
use App\Http\Controllers\DocumentGenerationController;
use App\Http\Controllers\Api\PipelineFlowController;
use App\Http\Controllers\LeadKanbanController;
use App\Http\Controllers\Api\MapDataController;
use App\Http\Controllers\DriverPortalController;
use App\Http\Middleware\DriverSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

// Fallback login route to satisfy middleware expecting a named "login" route.
Route::get('/login', function (): RedirectResponse {
    return redirect('/driver-panel/login');
})->name('login');

Route::get('/lead', [LeadController::class, 'create']);
Route::post('/lead', [LeadController::class, 'store']);

// Driver self-service check-call / doc upload form (public)
Route::view('/driver/check-call', 'driver.check-call')->name('driver.check-call.form');
Route::view('/driver/app', 'driver.app')->name('driver.app');

// Driver portal (legacy) removed in favor of Filament driver panel

// Admin pipeline flow (session-auth) for visual builder
Route::middleware(['web', \Filament\Http\Middleware\Authenticate::class, 'role:admin|staff'])->group(function () {
    Route::get('/admin/api/pipeline/flow', [PipelineFlowController::class, 'show'])->name('admin.pipeline.flow');
    Route::post('/admin/api/pipeline/flow', [PipelineFlowController::class, 'store'])->name('admin.pipeline.flow.save');
    Route::get('/admin/api/leads/kanban', [LeadKanbanController::class, 'index'])->name('admin.leads.kanban');
    Route::patch('/admin/api/leads/{lead}/kanban', [LeadKanbanController::class, 'move'])->name('admin.leads.kanban.move');
    Route::get('/admin/map-data', [MapDataController::class, 'index'])->name('admin.map-data');
});

// JSON map data for TMS (admin authenticated)
Route::middleware(['web', 'auth'])->get('/admin/tms-map-data', function () {
    return response()->json([
        'loads' => TransportBoard::mapData(),
    ]);
})->name('admin.tms-map-data');

// Serve driver OpenAPI spec (YAML)
Route::get('/openapi.yaml', function () {
    $path = base_path('docs/driver-api.openapi.yaml');
    if (!File::exists($path)) {
        abort(404);
    }
    return response(File::get($path), 200, ['Content-Type' => 'application/yaml']);
})->name('openapi.driver');

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

// Extended health check (compatible with Laravel's /up path)
Route::get('/up', function () {
    $checks = [
        'app' => true,
        'db' => false,
        'cache' => false,
        'storage' => false,
        'queue' => config('queue.default'),
        'cors' => [
            'origins' => config('cors.allowed_origins'),
            'patterns' => config('cors.allowed_origins_patterns'),
        ],
    ];
    $messages = [];

    try {
        DB::connection()->getPdo();
        $checks['db'] = true;
    } catch (\Throwable $e) {
        $messages[] = 'db:' . $e->getMessage();
    }

    try {
        Cache::store()->put('health_ping', 'ok', 5);
        $checks['cache'] = Cache::store()->get('health_ping') === 'ok';
    } catch (\Throwable $e) {
        $messages[] = 'cache:' . $e->getMessage();
    }

    try {
        $checks['storage'] = is_writable(storage_path('app'));
    } catch (\Throwable $e) {
        $messages[] = 'storage:' . $e->getMessage();
    }

    $status = ($checks['app'] === true)
        && ($checks['db'] === true)
        && ($checks['cache'] === true)
        && ($checks['storage'] === true);

    return response()->json([
        'ok' => $status,
        'checks' => $checks,
        'messages' => $messages,
    ], $status ? 200 : 503);
});
