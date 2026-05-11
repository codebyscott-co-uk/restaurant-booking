<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmationMail;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\RestaurantTable;
use App\Models\Service;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerBookingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_lookup_booking_with_reference_and_email(): void
    {
        $this->seed();
        $booking = $this->createManageableBooking();

        $this->post('/manage-booking', [
            'booking_reference' => strtolower($booking->booking_reference),
            'email' => 'manage@example.test',
        ])->assertRedirect(route('bookings.manage.show', [
            'booking' => $booking,
            'token' => $booking->customer_manage_token,
        ]));

        $this->get(route('bookings.manage.show', [
            'booking' => $booking,
            'token' => $booking->customer_manage_token,
        ]))
            ->assertOk()
            ->assertSee('Modify booking')
            ->assertSee('Cancel booking');
    }

    public function test_customer_manage_link_requires_valid_token(): void
    {
        $this->seed();
        $booking = $this->createManageableBooking();

        $this->get(route('bookings.manage.show', [
            'booking' => $booking,
            'token' => 'wrong-token',
        ]))->assertNotFound();

        $this->get('/booking/'.$booking->booking_reference)
            ->assertNotFound();
    }

    public function test_customer_can_modify_booking_before_policy_cutoff(): void
    {
        $this->seed();
        Mail::fake();
        $booking = $this->createManageableBooking();
        $venue = $booking->venue;
        $service = Service::where('name', 'Dinner')->firstOrFail();
        $date = now($venue->timezone)->next('Tuesday')->toDateString();

        $this->put(route('bookings.manage.update', [
            'booking' => $booking,
            'token' => $booking->customer_manage_token,
        ]), [
            'service_id' => $service->id,
            'party_size' => 3,
            'date' => $date,
            'time' => '18:00',
            'first_name' => 'Updated',
            'last_name' => 'Guest',
            'email' => 'updated@example.test',
            'phone' => '07000 444555',
            'special_requests' => 'Near the window.',
        ])->assertRedirect(route('bookings.manage.show', [
            'booking' => $booking,
            'token' => $booking->customer_manage_token,
        ]));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'party_size' => 3,
            'special_requests' => 'Near the window.',
        ]);
        $this->assertDatabaseHas('customers', ['email' => 'updated@example.test']);
        Mail::assertSent(BookingConfirmationMail::class);
    }

    public function test_customer_can_cancel_booking_before_policy_cutoff(): void
    {
        $this->seed();
        $booking = $this->createManageableBooking();

        $this->patch(route('bookings.manage.cancel', [
            'booking' => $booking,
            'token' => $booking->customer_manage_token,
        ]))->assertRedirect(route('bookings.manage.show', [
            'booking' => $booking,
            'token' => $booking->customer_manage_token,
        ]));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);
        $this->assertNotNull($booking->fresh()->cancelled_at);
    }

    public function test_customer_cannot_cancel_inside_policy_cutoff(): void
    {
        $this->seed();
        $venue = Venue::firstOrFail();
        $venue->update(['cancellation_notice_hours' => 48]);
        $booking = $this->createManageableBooking(now($venue->timezone)->addHours(24));

        $this->patch(route('bookings.manage.cancel', [
            'booking' => $booking,
            'token' => $booking->customer_manage_token,
        ]))->assertSessionHasErrors('booking');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
        ]);
    }

    private function createManageableBooking(?Carbon $startsAt = null): Booking
    {
        $venue = Venue::firstOrFail();
        $service = Service::where('name', 'Dinner')->firstOrFail();
        $startsAt ??= now($venue->timezone)->next('Monday')->setTime(18, 0);
        $customer = Customer::create([
            'first_name' => 'Manage',
            'last_name' => 'Guest',
            'email' => 'manage@example.test',
            'phone' => '07000 111222',
        ]);

        $booking = Booking::create([
            'venue_id' => $venue->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'booking_reference' => 'CBR'.Str::upper(Str::random(6)),
            'customer_manage_token' => Str::random(48),
            'party_size' => 2,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMinutes($service->default_duration_minutes),
            'status' => 'confirmed',
            'source' => 'web',
            'confirmed_at' => now(),
        ]);

        $booking->tables()->attach(RestaurantTable::where('name', 'T2')->firstOrFail());

        return $booking->fresh(['venue', 'customer', 'service', 'tables']);
    }
}
