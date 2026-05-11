<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\RestaurantTable;
use App\Models\Service;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BookingRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_booking_respects_minimum_lead_time(): void
    {
        $this->seed();
        $venue = Venue::firstOrFail();
        $venue->update(['minimum_lead_time_minutes' => 240]);
        $service = Service::where('name', 'Lunch')->firstOrFail();
        $date = today($venue->timezone)->toDateString();

        $this->get('/?date='.$date.'&party_size=2&service_id='.$service->id)
            ->assertOk()
            ->assertSee('No available times');
    }

    public function test_public_booking_respects_maximum_party_size(): void
    {
        $this->seed();
        Venue::firstOrFail()->update(['maximum_party_size' => 4]);
        $service = Service::where('name', 'Dinner')->firstOrFail();

        $this->post('/book', [
            'service_id' => $service->id,
            'party_size' => 5,
            'date' => now()->next('Monday')->toDateString(),
            'time' => '18:00',
            'first_name' => 'Large',
            'last_name' => 'Party',
            'email' => 'large@example.test',
            'phone' => '07111 777888',
        ])->assertSessionHasErrors('party_size');
    }

    public function test_public_booking_respects_maximum_covers_per_slot(): void
    {
        $this->seed();
        $venue = Venue::firstOrFail();
        $venue->update(['maximum_covers_per_slot' => 4]);
        $service = Service::where('name', 'Dinner')->firstOrFail();
        $date = now()->next('Monday')->toDateString();
        $customer = Customer::firstOrFail();

        Booking::create([
            'venue_id' => $venue->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'booking_reference' => 'CBRCAPS',
            'party_size' => 4,
            'starts_at' => Carbon::parse($date.' 18:00'),
            'ends_at' => Carbon::parse($date.' 20:00'),
            'status' => 'confirmed',
            'source' => 'phone',
            'confirmed_at' => now(),
        ]);

        $this->get('/?date='.$date.'&party_size=2&service_id='.$service->id)
            ->assertOk()
            ->assertDontSee('18:00');
    }

    public function test_large_party_can_use_joined_tables_when_enabled(): void
    {
        $this->seed();
        $venue = Venue::firstOrFail();
        $venue->update(['maximum_party_size' => 10, 'allow_joined_tables' => true]);
        $service = Service::where('name', 'Dinner')->firstOrFail();
        $service->update(['max_covers' => 10]);

        RestaurantTable::where('max_covers', '>=', 8)->update(['is_active' => false]);

        $response = $this->post('/book', [
            'service_id' => $service->id,
            'party_size' => 10,
            'date' => now()->next('Monday')->toDateString(),
            'time' => '18:00',
            'first_name' => 'Joined',
            'last_name' => 'Tables',
            'email' => 'joined@example.test',
            'phone' => '07111 999000',
        ]);

        $response->assertRedirect();

        $booking = Booking::whereHas('customer', fn ($query) => $query->where('email', 'joined@example.test'))->firstOrFail();

        $this->assertGreaterThan(1, $booking->tables()->count());
    }
}
