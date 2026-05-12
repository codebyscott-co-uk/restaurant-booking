<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\DiningArea;
use App\Models\OpeningHour;
use App\Models\RestaurantTable;
use App\Models\Service;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class MultiTenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_staff_only_see_their_own_venue_settings(): void
    {
        $this->seed();
        $secondVenue = Venue::create([
            'name' => 'Second Bistro',
            'slug' => 'second-bistro',
            'contact_email' => 'second@example.test',
        ]);
        $secondUser = User::create([
            'venue_id' => $secondVenue->id,
            'name' => 'Second Owner',
            'email' => 'second-owner@example.test',
            'password' => 'password',
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->actingAs($secondUser)
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('Second Bistro')
            ->assertDontSee('The Demo Table');
    }

    public function test_admin_staff_cannot_manage_another_venue_service(): void
    {
        $this->seed();
        $secondVenue = Venue::create([
            'name' => 'Second Bistro',
            'slug' => 'second-bistro',
        ]);
        $secondUser = User::create([
            'venue_id' => $secondVenue->id,
            'name' => 'Second Owner',
            'email' => 'second-owner@example.test',
            'password' => 'password',
            'role' => 'owner',
            'is_active' => true,
        ]);
        $firstVenueService = Service::where('name', 'Lunch')->firstOrFail();

        $this->actingAs($secondUser)
            ->get('/admin/services/'.$firstVenueService->id.'/edit')
            ->assertNotFound();
    }

    public function test_admin_staff_cannot_create_table_inside_another_venues_area(): void
    {
        $this->seed();
        $secondVenue = $this->createTenant('Second Bistro');
        $secondUser = $secondVenue->users()->firstOrFail();
        $firstVenueArea = DiningArea::where('name', 'Main Dining Room')->firstOrFail();

        $this->actingAs($secondUser)
            ->post('/admin/tables', [
                'dining_area_id' => $firstVenueArea->id,
                'name' => 'X1',
                'min_covers' => 1,
                'max_covers' => 2,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('dining_area_id');

        $this->assertDatabaseMissing('restaurant_tables', [
            'venue_id' => $secondVenue->id,
            'dining_area_id' => $firstVenueArea->id,
            'name' => 'X1',
        ]);
    }

    public function test_admin_staff_cannot_update_another_venues_booking_status_or_availability(): void
    {
        $this->seed();
        $secondVenue = $this->createTenant('Second Bistro');
        $secondUser = $secondVenue->users()->firstOrFail();
        $firstBooking = Booking::where('venue_id', '!=', $secondVenue->id)->firstOrFail();
        $firstOpeningHour = OpeningHour::where('venue_id', '!=', $secondVenue->id)->firstOrFail();

        $this->actingAs($secondUser)
            ->patch('/admin/bookings/'.$firstBooking->booking_reference.'/status', ['status' => 'cancelled'])
            ->assertNotFound();

        $this->assertDatabaseHas('bookings', [
            'id' => $firstBooking->id,
            'status' => $firstBooking->status,
        ]);

        $this->actingAs($secondUser)
            ->put('/admin/availability/hours', [
                'hours' => [
                    'foreign' => [
                        [
                            'id' => $firstOpeningHour->id,
                            'opens_at' => '09:00',
                            'closes_at' => '17:00',
                            'is_closed' => '0',
                        ],
                    ],
                ],
            ])
            ->assertSessionHasErrors('hours.foreign.0.id');
    }

    public function test_public_manage_routes_are_isolated_by_tenant_slug(): void
    {
        $this->seed();
        $secondVenue = $this->createTenant('Second Bistro');
        $booking = Booking::where('venue_id', '!=', $secondVenue->id)->firstOrFail();

        $this->get('/r/second-bistro/manage-booking/'.$booking->booking_reference.'/'.$booking->customer_manage_token)
            ->assertNotFound();

        $this->post('/r/second-bistro/manage-booking', [
            'booking_reference' => $booking->booking_reference,
            'email' => $booking->customer->email,
        ])
            ->assertSessionHasErrors('booking_reference');
    }

    public function test_customers_and_subscriptions_are_owned_by_their_venue(): void
    {
        $this->seed();
        $venue = Venue::where('slug', 'the-demo-table')->firstOrFail();
        $secondVenue = $this->createTenant('Second Bistro');

        Customer::whereNull('venue_id')->update(['venue_id' => $venue->id]);

        $subscription = TenantSubscription::create([
            'venue_id' => $secondVenue->id,
            'provider' => 'stripe',
            'provider_customer_id' => 'cus_second',
            'provider_subscription_id' => 'sub_second',
            'plan' => 'starter',
            'status' => 'trialing',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $this->assertTrue(Customer::firstOrFail()->belongsToVenue($venue));
        $this->assertTrue($subscription->belongsToVenue($secondVenue));
        $this->assertFalse($subscription->belongsToVenue($venue));
    }

    public function test_public_tenant_booking_page_and_api_use_venue_slug(): void
    {
        $this->seed();

        $this->get('/r/the-demo-table')
            ->assertOk()
            ->assertSee('Reserve your table at The Demo Table');

        $this->getJson('/api/v1/the-demo-table/venue')
            ->assertOk()
            ->assertJsonPath('data.slug', 'the-demo-table');

        $this->get('/r/the-demo-table/widget/bookings')
            ->assertOk()
            ->assertSee('The Demo Table');
    }

    private function createTenant(string $name): Venue
    {
        $slug = str($name)->slug()->toString();

        $venue = Venue::create([
            'name' => $name,
            'slug' => $slug,
            'contact_email' => str($slug)->replace('-', '').'@example.test',
            'maximum_party_size' => 8,
            'maximum_covers_per_slot' => 20,
            'timezone' => 'Europe/London',
        ]);

        User::create([
            'venue_id' => $venue->id,
            'name' => $name.' Owner',
            'email' => $slug.'-owner@example.test',
            'password' => 'password',
            'role' => 'owner',
            'is_active' => true,
        ]);

        $area = DiningArea::create([
            'venue_id' => $venue->id,
            'name' => 'Main Room',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $table = RestaurantTable::create([
            'venue_id' => $venue->id,
            'dining_area_id' => $area->id,
            'name' => 'S1',
            'min_covers' => 1,
            'max_covers' => 4,
            'is_active' => true,
        ]);

        $service = Service::create([
            'venue_id' => $venue->id,
            'name' => 'Dinner',
            'starts_at' => '17:00',
            'ends_at' => '22:00',
            'slot_interval_minutes' => 30,
            'default_duration_minutes' => 120,
            'min_covers' => 1,
            'max_covers' => 8,
            'is_active' => true,
        ]);

        foreach (range(0, 6) as $day) {
            OpeningHour::create([
                'venue_id' => $venue->id,
                'service_id' => $service->id,
                'day_of_week' => $day,
                'opens_at' => $service->starts_at,
                'closes_at' => $service->ends_at,
                'is_closed' => false,
            ]);
        }

        $customer = Customer::create([
            'venue_id' => $venue->id,
            'first_name' => 'Tenant',
            'last_name' => 'Guest',
            'email' => $slug.'-guest@example.test',
            'phone' => '07000 000000',
        ]);

        $booking = Booking::create([
            'venue_id' => $venue->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'booking_reference' => 'CBR'.Str::upper(Str::random(6)),
            'customer_manage_token' => Str::random(48),
            'party_size' => 2,
            'starts_at' => Carbon::today('Europe/London')->addDay()->setTime(19, 0),
            'ends_at' => Carbon::today('Europe/London')->addDay()->setTime(21, 0),
            'status' => 'confirmed',
            'source' => 'web',
            'confirmed_at' => now(),
        ]);
        $booking->tables()->attach($table);

        return $venue;
    }
}
