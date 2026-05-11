<?php

namespace Tests\Feature;

use App\Mail\BookingCancelledMail;
use App\Mail\BookingConfirmationMail;
use App\Mail\BookingModifiedMail;
use App\Mail\BookingReminderMail;
use App\Mail\NewBookingStaffAlertMail;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_emails_render_with_branded_layout(): void
    {
        $this->seed();
        $booking = Booking::with('venue', 'customer', 'service', 'tables.diningArea')->firstOrFail();

        foreach ([
            new BookingConfirmationMail($booking),
            new BookingModifiedMail($booking),
            new BookingCancelledMail($booking),
            new BookingReminderMail($booking),
            new NewBookingStaffAlertMail($booking),
        ] as $mail) {
            $html = $mail->render();

            $this->assertStringContainsString($booking->venue->name, $html);
            $this->assertStringContainsString($booking->booking_reference, $html);
            $this->assertStringContainsString('Restaurant booking software by Code by Scott.', $html);
        }
    }
}
