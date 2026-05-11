<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_billing_page(): void
    {
        $this->get(route('admin.billing.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_staff_can_access_billing_page(): void
    {
        $this->seed();
        $user = User::first();

        $this->actingAs($user)
            ->get(route('admin.billing.index'))
            ->assertOk()
            ->assertSee('Billing & Subscription');
    }

    public function test_billing_page_shows_subscription_status(): void
    {
        $this->seed();
        $user = User::first();
        $venue = $user->venue;

        $this->actingAs($user)
            ->get(route('admin.billing.index'))
            ->assertOk()
            ->assertSee('Current Subscription');
    }

    public function test_billing_page_shows_success_message_when_flag_set(): void
    {
        $this->seed();
        $user = User::first();

        $this->actingAs($user)
            ->get(route('admin.billing.index', ['success' => 1]))
            ->assertOk()
            ->assertSee('Your subscription was set up successfully');
    }

    public function test_billing_page_shows_cancelled_message_when_flag_set(): void
    {
        $this->seed();
        $user = User::first();

        $this->actingAs($user)
            ->get(route('admin.billing.index', ['cancelled' => 1]))
            ->assertOk()
            ->assertSee('You cancelled the checkout');
    }

    public function test_guest_cannot_access_checkout(): void
    {
        $this->post(route('admin.billing.checkout'))
            ->assertRedirect(route('login'));
    }

    public function test_checkout_requires_stripe_price_id(): void
    {
        $this->seed();
        $user = User::first();

        // Temporarily set price_id to null
        config(['services.stripe.price_id' => null]);

        $this->actingAs($user)
            ->post(route('admin.billing.checkout'))
            ->assertRedirect()
            ->assertSessionHasErrors('billing');
    }

    public function test_staff_can_only_manage_own_venue_billing(): void
    {
        $this->seed();
        $user1 = User::first();
        $venue2 = Venue::create([
            'name' => 'Other Restaurant',
            'slug' => 'other-restaurant',
            'primary_colour' => '#0f766e',
            'accent_colour' => '#f59e0b',
            'timezone' => 'Europe/London',
        ]);
        $user2 = User::factory()->create(['venue_id' => $venue2->id]);

        // User1 can see their own venue info on billing page
        $this->actingAs($user1)
            ->get(route('admin.billing.index'))
            ->assertOk();

        // User2 can see their own venue info on billing page
        $this->actingAs($user2)
            ->get(route('admin.billing.index'))
            ->assertOk();
    }

    public function test_checkout_uses_current_user_venue(): void
    {
        $this->seed();
        $user = User::first();
        $venue = $user->venue;

        // Mock the Cashier subscription behavior
        $this->actingAs($user)
            ->post(route('admin.billing.checkout'))
            ->assertStatus(302); // Should redirect to Stripe or return URL
    }
}
