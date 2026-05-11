<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingModifiedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking)
    {
        $this->booking->loadMissing('venue', 'customer', 'service', 'tables.diningArea');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your booking at '.$this->booking->venue->name.' has been updated',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bookings.modified',
        );
    }
}
