<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingCreated extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        $ical = $this->buildIcs($this->booking);

        return $this->subject('New Booking Request: ' . $this->booking->title)
            ->view('emails.booking-created', [
                'booking' => $this->booking,
            ])
            ->attachData($ical, 'booking.ics', [
                'mime' => 'text/calendar; charset=utf-8',
            ]);
    }

    protected function buildIcs(Booking $booking): string
    {
        $uid = uniqid('booking-', true);
        $dtStart = $booking->start_at->format('Ymd\THis\Z');
        $dtEnd = ($booking->end_at ?? $booking->start_at)->format('Ymd\THis\Z');

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//DispatchPro//Booking//EN',
            'BEGIN:VEVENT',
            "UID:$uid",
            "DTSTAMP:$dtStart",
            "DTSTART:$dtStart",
            "DTEND:$dtEnd",
            'SUMMARY:' . $booking->title,
            'DESCRIPTION:' . ($booking->notes ?? 'Booking request'),
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return implode("\r\n", $lines);
    }
}
