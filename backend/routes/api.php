<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarrierDraftController;
use App\Http\Controllers\Api\CarrierProfileController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LandingContentController;
use App\Http\Controllers\Api\BookingController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

// Public carrier onboarding flow (reference-code based)
Route::prefix('carrier-profiles')->middleware('throttle:60,1')->group(function () {
    Route::post('draft', [CarrierDraftController::class, 'store']);
    Route::get('draft/{id}', [CarrierDraftController::class, 'show']);
    Route::post('draft/{id}/documents', [DocumentController::class, 'store']);
    Route::get('draft/{id}/documents', [DocumentController::class, 'index']);
    Route::post('draft/{id}/submit', [CarrierDraftController::class, 'submit']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('dashboard/summary', [DashboardController::class, 'summary']);
    Route::get('loads', [DashboardController::class, 'loads']);
    Route::post('loads/{id}/request', [DashboardController::class, 'requestLoad']);
});

Route::middleware(['auth:sanctum', 'role:admin|staff'])->group(function () {
    Route::post('carrier-documents/{carrierDocument}/review', [DocumentController::class, 'review']);
});

// Public landing page content
Route::get('landing-page', [LandingContentController::class, 'index']);
Route::post('bookings', [BookingController::class, 'store'])
    ->middleware('throttle:20,1'); // basic rate limit to protect booking form

Route::middleware('auth:sanctum')->group(function () {
    Route::get('bookings', [BookingController::class, 'index']);
});

// Carrier profile submission (public; ties to authenticated user if present)
Route::post('carrier-profile', [CarrierProfileController::class, 'store'])
    ->middleware('throttle:20,1');
