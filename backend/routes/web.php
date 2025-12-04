<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadController;
use App\Filament\Pages\TransportBoard;
use App\Events\TmsMapUpdated;
use App\Models\Load;
use App\Models\LoadStop;
use App\Http\Controllers\DocumentGenerationController;

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

// Generate PDF for loads
Route::middleware(['web', 'auth'])->get('/admin/documents/loads/{load}/pdf', [DocumentGenerationController::class, 'loadPdf'])->name('admin.documents.loads.pdf');
Route::middleware(['web', 'auth'])->get('/admin/documents/invoices/{invoice}/pdf', [DocumentGenerationController::class, 'invoicePdf'])->name('admin.documents.invoices.pdf');
Route::middleware(['web', 'auth'])->get('/admin/documents/settlements/{settlement}/pdf', [DocumentGenerationController::class, 'settlementPdf'])->name('admin.documents.settlements.pdf');
