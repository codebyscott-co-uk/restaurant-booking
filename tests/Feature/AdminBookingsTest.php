<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBookingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_manual_booking(): void
    {
        $this->seed();

        $this->get('/admin/bookings/create')->assertRedirect('/staff/login');
    }

    public function test_staff_can_create_manual_phone_booking(): void
    {
        $this->seed();
        $service = Service::where('name', 'Lunch')->firstOrFail();
        $nextMonday = now()->next('Monday')->toDateString();

        $this->actingAs(User::first())
            ->post('/admin/bookings', [
                'service_id' => $service->id,
                'party_size' => 3,
                'date' => $nextMonday,
                'time' => '12:00',
                'first_name' => 'Clara',
                'last_name' => 'Osborne',
                'email' => 'clara@example.test',
                'phone' => '07111 333444',
                'source' => 'phone',
                'status' => 'confirmed',
                'special_requests' => 'High chair.',
                'internal_notes' => 'Regular guest.',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/diary?date='.$nextMonday);

        $this->assertDatabaseHas('customers', ['email' => 'clara@example.test']);
        $this->assertDatabaseHas('bookings', [
            'party_size' => 3,
            'source' => 'phone',
            'status' => 'confirmed',
            'internal_notes' => 'Regular guest.',
        ]);
    }

    public function test_manual_booking_cannot_double_book_a_table(): void
    {
        $this->seed();
        $service = Service::where('name', 'Dinner')->firstOrFail();
        $today = now()->toDateString();
        $venue = $service->venue;
        $venue->update(['allow_joined_tables' => false]);
        $eightTop = $venue->tables()
            ->where('min_covers', '<=', 8)
            ->where('max_covers', '>=', 8)
            ->firstOrFail();

        $blockingBooking = Booking::create([
            'venue_id' => $venue->id,
            'customer_id' => Booking::firstOrFail()->customer_id,
            'service_id' => $service->id,
            'booking_reference' => 'CBRBLOCK',
            'party_size' => 8,
            'starts_at' => Carbon::parse($today.' 18:00'),
            'ends_at' => Carbon::parse($today.' 20:00'),
            'status' => 'confirmed',
            'source' => 'phone',
            'confirmed_at' => now(),
        ]);

        $blockingBooking->tables()->attach($eightTop);

        $this->actingAs(User::first())
            ->post('/admin/bookings', [
                'service_id' => $service->id,
                'party_size' => 8,
                'date' => $today,
                'time' => '18:00',
                'first_name' => 'Double',
                'last_name' => 'Booked',
                'email' => 'double@example.test',
                'phone' => '07111 555666',
                'source' => 'phone',
                'status' => 'confirmed',
            ])
            ->assertSessionHasErrors('time');

        $this->assertDatabaseMissing('customers', ['email' => 'double@example.test']);
    }

    public function test_diary_shows_add_booking_action(): void
    {
        $this->seed();

        $this->actingAs(User::first())
            ->get('/admin/diary')
            ->assertOk()
            ->assertSee('Add booking');
    }

    public function test_guest_cannot_update_booking_status(): void
    {
        $this->seed();
        $booking = Booking::firstOrFail();

        $this->patch('/admin/bookings/'.$booking->booking_reference.'/status', [
            'status' => 'cancelled',
        ])->assertRedirect('/staff/login');
    }

    public function test_staff_can_update_booking_status_from_diary(): void
    {
        $this->seed();
        $booking = Booking::where('status', 'confirmed')->firstOrFail();

        $this->actingAs(User::first())
            ->patch('/admin/bookings/'.$booking->booking_reference.'/status', [
                'status' => 'cancelled',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/diary?date='.$booking->starts_at->toDateString());

        $booking->refresh();

        $this->assertSame('cancelled', $booking->status);
        $this->assertNotNull($booking->cancelled_at);
    }
}
