<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CustomerCrmTest extends TestCase
{
    use RefreshDatabase;

    public function test_starter_users_see_customer_crm_upgrade_prompt(): void
    {
        $this->seed();

        $this->actingAs(User::firstOrFail())
            ->get('/admin/customers')
            ->assertRedirect('/admin/upgrade/customer_crm');

        $this->actingAs(User::firstOrFail())
            ->get('/admin/upgrade/customer_crm')
            ->assertOk()
            ->assertSee('Customer CRM')
            ->assertSee('Professional');
    }

    public function test_professional_and_premium_users_can_access_crm(): void
    {
        $this->seed();
        $venue = Venue::firstOrFail();
        $user = User::firstOrFail();

        $this->setLocalPlan($venue, 'professional');

        $this->actingAs($user)
            ->get('/admin/customers')
            ->assertOk()
            ->assertSee('Customer directory')
            ->assertSee('Amelia Hart');

        $this->setLocalPlan($venue, 'premium');

        $this->actingAs($user)
            ->get('/admin/customers')
            ->assertOk()
            ->assertSee('Customer directory');
    }

    public function test_customer_profiles_are_tenant_scoped(): void
    {
        $this->seed();
        $venue = Venue::firstOrFail();
        $user = User::firstOrFail();
        $this->setLocalPlan($venue, 'professional');

        $otherVenue = Venue::create(['name' => 'Other Venue', 'slug' => 'other-venue', 'timezone' => 'Europe/London']);
        $this->setLocalPlan($otherVenue, 'professional');
        $otherCustomer = Customer::create([
            'venue_id' => $otherVenue->id,
            'first_name' => 'Hidden',
            'last_name' => 'Guest',
            'email' => 'hidden@example.test',
            'phone' => '07000 000000',
        ]);

        $this->actingAs($user)
            ->get('/admin/customers/'.$otherCustomer->id)
            ->assertNotFound();

        $this->actingAs($user)
            ->put('/admin/customers/'.$otherCustomer->id, [
                'first_name' => 'Changed',
                'last_name' => 'Guest',
                'email' => 'hidden@example.test',
                'phone' => '07000 000000',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('customers', [
            'id' => $otherCustomer->id,
            'first_name' => 'Hidden',
        ]);
    }

    public function test_customer_search_is_tenant_scoped(): void
    {
        $this->seed();
        $venue = Venue::firstOrFail();
        $this->setLocalPlan($venue, 'professional');

        Customer::create([
            'venue_id' => Venue::create(['name' => 'Search Other', 'slug' => 'search-other', 'timezone' => 'Europe/London'])->id,
            'first_name' => 'Zelda',
            'last_name' => 'Outside',
            'email' => 'zelda@example.test',
            'phone' => '07111 222333',
        ]);

        $this->actingAs(User::firstOrFail())
            ->get('/admin/customers?search=Zelda')
            ->assertOk()
            ->assertDontSee('Zelda Outside');
    }

    public function test_customer_can_be_created_and_updated_with_crm_fields(): void
    {
        $this->seed();
        $venue = Venue::firstOrFail();
        $this->setLocalPlan($venue, 'professional');

        $this->actingAs(User::firstOrFail())
            ->post('/admin/customers', [
                'first_name' => 'Priya',
                'last_name' => 'Kapoor',
                'email' => 'priya@example.test',
                'phone' => '07700 111222',
                'is_vip' => '1',
                'allergies' => 'Peanut allergy.',
                'dietary_requirements' => 'Vegetarian.',
                'preferences' => 'Prefers booth seating.',
                'notes' => 'Regular Friday guest.',
            ])
            ->assertRedirect();

        $customer = Customer::where('email', 'priya@example.test')->firstOrFail();

        $this->actingAs(User::firstOrFail())
            ->put('/admin/customers/'.$customer->id, [
                'first_name' => 'Priya',
                'last_name' => 'Kapoor',
                'email' => 'priya@example.test',
                'phone' => '07700 111222',
                'preferences' => 'Prefers terrace seating.',
                'notes' => 'Likes a quiet table.',
            ])
            ->assertRedirect('/admin/customers/'.$customer->id);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'venue_id' => $venue->id,
            'is_vip' => false,
            'preferences' => 'Prefers terrace seating.',
        ]);
    }

    public function test_staff_booking_creation_links_to_existing_current_venue_customer(): void
    {
        $this->seed();
        Mail::fake();

        $venue = Venue::firstOrFail();
        $this->setLocalPlan($venue, 'professional');
        $customer = Customer::where('email', 'amelia@example.test')->firstOrFail();
        $service = Service::where('venue_id', $venue->id)->where('name', 'Dinner')->firstOrFail();
        $date = Carbon::now('Europe/London')->next(Carbon::MONDAY)->toDateString();

        $this->actingAs(User::firstOrFail())
            ->post('/admin/bookings', [
                'service_id' => $service->id,
                'party_size' => 2,
                'date' => $date,
                'time' => '18:00',
                'first_name' => 'Amelia',
                'last_name' => 'Hart',
                'email' => 'amelia@example.test',
                'phone' => '07123 456789',
                'source' => 'phone',
                'status' => 'confirmed',
                'special_requests' => 'Near the window.',
                'customer_notes' => 'Updated CRM note from phone call.',
            ])
            ->assertRedirect('/admin/diary?date='.$date);

        $this->assertSame(1, Customer::where('venue_id', $venue->id)->where('email', 'amelia@example.test')->count());
        $this->assertDatabaseHas('bookings', [
            'customer_id' => $customer->id,
            'special_requests' => 'Near the window.',
        ]);
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'notes' => 'Updated CRM note from phone call.',
        ]);
    }

    public function test_booking_drawer_only_shows_crm_data_when_plan_allows_it(): void
    {
        $this->seed();
        $venue = Venue::firstOrFail();
        $date = Booking::whereHas('customer', fn ($query) => $query->where('email', 'amelia@example.test'))
            ->firstOrFail()
            ->starts_at
            ->toDateString();

        $this->actingAs(User::firstOrFail())
            ->get('/admin/diary?date='.$date)
            ->assertOk()
            ->assertDontSee('CRM profile')
            ->assertDontSee('Shellfish allergy');

        $this->setLocalPlan($venue, 'professional');

        $this->actingAs(User::firstOrFail())
            ->get('/admin/diary?date='.$date)
            ->assertOk()
            ->assertSee('CRM profile')
            ->assertSee('Shellfish allergy');
    }

    private function setLocalPlan(Venue $venue, string $plan): void
    {
        TenantSubscription::updateOrCreate(
            ['venue_id' => $venue->id, 'provider' => 'stripe'],
            ['plan' => $plan, 'status' => 'active', 'trial_ends_at' => null]
        );
    }
}
