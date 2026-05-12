<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmationMail;
use App\Models\Booking;
use App\Models\DiningArea;
use App\Models\RestaurantTable;
use App\Models\Service;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
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
        Mail::fake();
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
        $this->assertNotNull(Booking::whereHas('customer', fn ($query) => $query->where('email', 'clara@example.test'))->firstOrFail()->customer_manage_token);
        Mail::assertSent(BookingConfirmationMail::class, fn (BookingConfirmationMail $mail) => $mail->hasTo('clara@example.test'));
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
            ->assertSee('Add booking')
            ->assertSee('Total bookings')
            ->assertSee('Lunch');
    }

    public function test_staff_can_view_week_diary_and_filter_by_service(): void
    {
        $this->seed();
        $service = Service::where('name', 'Lunch')->firstOrFail();

        $this->actingAs(User::first())
            ->get('/admin/diary?view=week&service_id='.$service->id)
            ->assertOk()
            ->assertSee('Selected period')
            ->assertSee('Lunch')
            ->assertSee('No dinner bookings.');
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

    public function test_diary_filters_by_status_search_and_list_view(): void
    {
        $this->seed();

        $booking = Booking::where('status', 'confirmed')->firstOrFail();

        $this->actingAs(User::first())
            ->get('/admin/diary?display=list&status=confirmed&search='.$booking->customer->last_name)
            ->assertOk()
            ->assertSee('Booking list')
            ->assertSee($booking->booking_reference)
            ->assertSee($booking->customer->full_name);

        $this->actingAs(User::first())
            ->get('/admin/diary?status=no_show&search=not-a-real-guest')
            ->assertOk()
            ->assertSee('No bookings match these filters.');
    }

    public function test_staff_can_edit_booking_notes_and_table_assignment(): void
    {
        $this->seed();
        $booking = Booking::where('status', 'confirmed')->firstOrFail();
        $service = $booking->service;
        $table = $booking->venue->tables()
            ->where('id', '!=', $booking->tables->first()->id)
            ->where('max_covers', '>=', $booking->party_size)
            ->firstOrFail();

        $this->actingAs(User::first())
            ->put('/admin/bookings/'.$booking->booking_reference, [
                'service_id' => $service->id,
                'party_size' => $booking->party_size,
                'date' => $booking->starts_at->toDateString(),
                'time' => $booking->starts_at->format('H:i'),
                'first_name' => $booking->customer->first_name,
                'last_name' => $booking->customer->last_name,
                'email' => $booking->customer->email,
                'phone' => $booking->customer->phone,
                'source' => 'staff',
                'status' => 'seated',
                'special_requests' => 'Near the window.',
                'internal_notes' => 'VIP guest.',
                'customer_notes' => 'Likes booth seating.',
                'table_ids' => [$table->id],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/diary?date='.$booking->starts_at->toDateString());

        $booking->refresh();

        $this->assertSame('seated', $booking->status);
        $this->assertSame('staff', $booking->source);
        $this->assertSame('VIP guest.', $booking->internal_notes);
        $this->assertTrue($booking->tables->contains($table));
    }

    public function test_table_assignment_rejects_another_venues_table(): void
    {
        $this->seed();
        $booking = Booking::where('status', 'confirmed')->firstOrFail();
        $otherVenue = Venue::create(['name' => 'Other Venue', 'slug' => 'other-venue', 'timezone' => 'Europe/London']);
        $otherArea = DiningArea::create(['venue_id' => $otherVenue->id, 'name' => 'Other Room']);
        $otherTable = RestaurantTable::create([
            'venue_id' => $otherVenue->id,
            'dining_area_id' => $otherArea->id,
            'name' => 'OT1',
            'min_covers' => 1,
            'max_covers' => 8,
        ]);

        $this->actingAs(User::first())
            ->put('/admin/bookings/'.$booking->booking_reference, [
                'service_id' => $booking->service_id,
                'party_size' => $booking->party_size,
                'date' => $booking->starts_at->toDateString(),
                'time' => $booking->starts_at->format('H:i'),
                'first_name' => $booking->customer->first_name,
                'last_name' => $booking->customer->last_name,
                'email' => $booking->customer->email,
                'phone' => $booking->customer->phone,
                'source' => $booking->source,
                'status' => $booking->status,
                'table_ids' => [$otherTable->id],
            ])
            ->assertSessionHasErrors('table_ids.0');
    }

    public function test_starter_can_access_core_bookings_but_not_advanced_reports(): void
    {
        $this->seed();

        $this->actingAs(User::first())
            ->get('/admin/diary')
            ->assertOk();

        $this->actingAs(User::first())
            ->get('/admin/reports')
            ->assertRedirect('/admin/upgrade/analytics');
    }
}
