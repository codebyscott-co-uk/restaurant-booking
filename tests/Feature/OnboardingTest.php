<?php

namespace Tests\Feature;

use App\Models\DiningArea;
use App\Models\OpeningHour;
use App\Models\RestaurantTable;
use App\Models\Service;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_create_restaurant_workspace(): void
    {
        $this->seed();

        $this->post('/signup', [
            'business_name' => 'Harbour House',
            'contact_email' => 'bookings@harbour.test',
            'phone' => '01234 555777',
            'city' => 'Brighton',
            'owner_name' => 'Maya Lewis',
            'owner_email' => 'maya@harbour.test',
            'password' => 'Strongpass123',
            'password_confirmation' => 'Strongpass123',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/settings');

        $venue = Venue::where('slug', 'harbour-house')->firstOrFail();
        $owner = User::where('email', 'maya@harbour.test')->firstOrFail();

        $this->assertAuthenticatedAs($owner);
        $this->assertSame($venue->id, $owner->venue_id);
        $this->assertSame('owner', $owner->role);
        $this->assertSame('Brighton', $venue->city);
        $this->assertSame('bookings@harbour.test', $venue->contact_email);
        $this->assertSame(2, Service::where('venue_id', $venue->id)->count());
        $this->assertSame(1, DiningArea::where('venue_id', $venue->id)->count());
        $this->assertSame(4, RestaurantTable::where('venue_id', $venue->id)->count());
        $this->assertSame(14, OpeningHour::where('venue_id', $venue->id)->count());
        $this->assertTrue(TenantSubscription::where('venue_id', $venue->id)->where('provider', 'stripe')->where('status', 'trialing')->exists());
    }

    public function test_onboarding_creates_unique_venue_slug(): void
    {
        $this->seed();

        Venue::create([
            'name' => 'Harbour House',
            'slug' => 'harbour-house',
            'timezone' => 'Europe/London',
        ]);

        $this->post('/signup', [
            'business_name' => 'Harbour House',
            'contact_email' => 'bookings@harbour-two.test',
            'owner_name' => 'Second Owner',
            'owner_email' => 'owner@harbour-two.test',
            'password' => 'Strongpass123',
            'password_confirmation' => 'Strongpass123',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('venues', [
            'name' => 'Harbour House',
            'slug' => 'harbour-house-2',
        ]);
    }

    public function test_signup_requires_unique_owner_email(): void
    {
        $this->seed();

        $this->post('/signup', [
            'business_name' => 'Duplicate Bistro',
            'contact_email' => 'bookings@duplicate.test',
            'owner_name' => 'Restaurant Admin',
            'owner_email' => 'hello@codebyscott.co.uk',
            'password' => 'Strongpass123',
            'password_confirmation' => 'Strongpass123',
        ])
            ->assertSessionHasErrors('owner_email');
    }
}
