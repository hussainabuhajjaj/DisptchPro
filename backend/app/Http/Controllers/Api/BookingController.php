<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Mail\BookingCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max((int) $request->query('per_page', 20), 1);
        $bookings = Booking::orderByDesc('start_at')
            ->paginate($perPage);

        return response()->json([
            'bookings' => $bookings->items(),
            'meta' => [
                'total' => $bookings->total(),
                'page' => $bookings->currentPage(),
                'per_page' => $bookings->perPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:call,onboarding,demo'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'carrier_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $booking = Booking::create([
            ...$validated,
            'status' => 'pending',
        ]);

        $notifyEmail = config('mail.booking_notify_to') ?? env('BOOKING_NOTIFICATION_EMAIL');
        if ($notifyEmail) {
            Mail::to($notifyEmail)->send(new BookingCreated($booking));
        }

        return response()->json([
            'success' => true,
            'booking' => $booking,
        ], 201);
    }
}
