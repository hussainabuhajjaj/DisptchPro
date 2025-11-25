@php
    /** @var \App\Models\Booking $booking */
@endphp

<p><strong>New booking received</strong></p>
<ul>
    <li><strong>Title:</strong> {{ $booking->title }}</li>
    <li><strong>Type:</strong> {{ ucfirst($booking->type) }}</li>
    <li><strong>Status:</strong> {{ ucfirst($booking->status) }}</li>
    <li><strong>Start:</strong> {{ $booking->start_at }}</li>
    @if($booking->end_at)
        <li><strong>End:</strong> {{ $booking->end_at }}</li>
    @endif
    @if($booking->carrier_name)
        <li><strong>Carrier:</strong> {{ $booking->carrier_name }}</li>
    @endif
    @if($booking->phone)
        <li><strong>Phone:</strong> {{ $booking->phone }}</li>
    @endif
    @if($booking->email)
        <li><strong>Email:</strong> {{ $booking->email }}</li>
    @endif
</ul>
@if($booking->notes)
    <p><strong>Notes:</strong> {{ $booking->notes }}</p>
@endif

<p>An .ics calendar file is attached.</p>
