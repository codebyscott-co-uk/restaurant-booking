<?php

namespace Tests\Feature;

use App\Models\TenantSubscription;
use App\Models\User;
use App\Models\Venue;
use App\Services\Billing\BillingPlans;
use App\Services\Billing\StripeBillingGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Cashier\Subscription;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'resora_billing.trial_days' => 14,
            'resora_billing.plans.starter.stripe_price_id' => 'price_starter_test',
            'resora_billing.plans.professional.stripe_price_id' => 'price_professional_test',
            'resora_billing.plans.premium.stripe_price_id' => 'price_premium_test',
        ]);
    }

    public function test_plan_config_loads_all_stripe_price_ids(): void
    {
        $plans = app(BillingPlans::class);

        $this->assertSame(['starter', 'professional', 'premium'], $plans->slugs());
        $this->assertSame(29, $plans->get('starter')['price']);
        $this->assertSame(59, $plans->get('professional')['price']);
        $this->assertSame(99, $plans->get('premium')['price']);
        $this->assertSame('price_professional_test', $plans->get('professional')['stripe_price_id']);
        $this->assertTrue($plans->get('professional')['recommended']);
    }

    public function test_billing_page_is_accessible_without_active_cashier_subscription(): void
    {
        $this->seed();

        $this->actingAs(User::first())
            ->get('/admin/billing')
            ->assertOk()
            ->assertSee('Subscription')
            ->assertSee('Starter')
            ->assertSee('Professional')
            ->assertSee('Premium');
    }

    public function test_billing_success_page_syncs_completed_checkout_session(): void
    {
        $this->seed();
        $venue = Venue::firstOrFail();
        TenantSubscription::where('venue_id', $venue->id)->delete();

        $this->mock(StripeBillingGateway::class, function ($mock) use ($venue) {
            $mock->shouldReceive('syncCheckoutSession')
                ->once()
                ->withArgs(fn ($actualVenue, $sessionId) => $actualVenue->is($venue)
                    && $sessionId === 'cs_test_premium')
                ->andReturnUsing(function () use ($venue) {
                    $this->cashierSubscription($venue, 'price_premium_test');

                    return true;
                });
        });

        $this->actingAs(User::first())
            ->get('/admin/billing?success=1&session_id=cs_test_premium')
            ->assertOk()
            ->assertSee('Your subscription has been activated.')
            ->assertSee('Premium');
    }

    public function test_checkout_route_is_protected(): void
    {
        $this->post('/admin/billing/checkout/starter')
            ->assertRedirect('/staff/login');
    }

    public function test_checkout_uses_current_venue_plan_price_and_trial(): void
    {
        $this->seed();
        $venue = Venue::firstOrFail();
        $user = User::firstOrFail();

        $this->mock(StripeBillingGateway::class, function ($mock) use ($venue) {
            $mock->shouldReceive('checkout')
                ->once()
                ->withArgs(fn ($actualVenue, $plan, $trialEligible, $trialDays) => $actualVenue->is($venue)
                    && $plan['slug'] === 'professional'
                    && $plan['stripe_price_id'] === 'price_professional_test'
                    && $trialEligible === true
                    && $trialDays === 14)
                ->andReturn(redirect('https://checkout.stripe.test/session'));
        });

        $this->actingAs($user)
            ->post('/admin/billing/checkout/professional')
            ->assertRedirect('https://checkout.stripe.test/session');
    }

    public function test_checkout_applies_only_to_current_users_venue(): void
    {
        $this->seed();
        $secondVenue = Venue::create([
            'name' => 'Second Venue',
            'slug' => 'second-venue',
            'timezone' => 'Europe/London',
        ]);
        $secondUser = User::create([
            'venue_id' => $secondVenue->id,
            'name' => 'Second Owner',
            'email' => 'second@example.test',
            'password' => 'password',
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->mock(StripeBillingGateway::class, function ($mock) use ($secondVenue) {
            $mock->shouldReceive('checkout')
                ->once()
                ->withArgs(fn ($actualVenue) => $actualVenue->is($secondVenue))
                ->andReturn(redirect('https://checkout.stripe.test/second'));
        });

        $this->actingAs($secondUser)
            ->post('/admin/billing/checkout/starter')
            ->assertRedirect('https://checkout.stripe.test/second');
    }

    public function test_duplicate_active_subscriptions_are_prevented(): void
    {
        $this->seed();
        $venue = Venue::firstOrFail();
        $this->cashierSubscription($venue, 'price_starter_test', 'active');

        $this->mock(StripeBillingGateway::class, function ($mock) {
            $mock->shouldNotReceive('checkout');
        });

        $this->actingAs(User::first())
            ->post('/admin/billing/checkout/professional')
            ->assertRedirect('/admin/billing')
            ->assertSessionHas('status');
    }

    public function test_trial_access_logic_accepts_local_trial_and_grace_period(): void
    {
        $this->seed();
        $venue = Venue::firstOrFail();

        $this->assertTrue($venue->fresh('activeSubscription')->hasPlatformAccess());

        TenantSubscription::where('venue_id', $venue->id)->update([
            'status' => 'cancelled',
            'trial_ends_at' => now()->subDay(),
        ]);

        $this->assertFalse($venue->fresh('activeSubscription')->hasPlatformAccess());

        $this->cashierSubscription($venue, 'price_starter_test', 'active', now()->addWeek());

        $this->assertTrue($venue->fresh()->hasPlatformAccess());
    }

    public function test_grace_period_subscription_shows_resume_action(): void
    {
        $this->seed();
        $venue = Venue::firstOrFail();
        TenantSubscription::where('venue_id', $venue->id)->delete();
        $this->cashierSubscription($venue, 'price_starter_test', 'active', now()->addWeek());

        $this->actingAs(User::first())
            ->get('/admin/billing')
            ->assertOk()
            ->assertSee('Resume subscription');
    }

    public function test_starter_cannot_access_professional_features(): void
    {
        $this->seed();

        $this->actingAs(User::first())
            ->get('/admin/customers')
            ->assertRedirect('/admin/upgrade/customer_crm');

        $this->actingAs(User::first())
            ->get('/admin/upgrade/customer_crm')
            ->assertOk()
            ->assertSee('Customer CRM')
            ->assertSee('Professional');
    }

    public function test_professional_cannot_access_premium_features(): void
    {
        $this->seed();
        $venue = Venue::firstOrFail();
        TenantSubscription::where('venue_id', $venue->id)->delete();
        $this->cashierSubscription($venue, 'price_professional_test');

        $this->actingAs(User::first())
            ->get('/admin/waitlist')
            ->assertRedirect('/admin/upgrade/waitlist');
    }

    public function test_premium_can_access_all_feature_placeholders(): void
    {
        $this->seed();
        $venue = Venue::firstOrFail();
        TenantSubscription::where('venue_id', $venue->id)->delete();
        $this->cashierSubscription($venue, 'price_premium_test');

        $this->actingAs(User::first())
            ->get('/admin/customers')
            ->assertOk()
            ->assertSee('Customer CRM');

        $this->actingAs(User::first())
            ->get('/admin/waitlist')
            ->assertOk()
            ->assertSee('Premium waitlist');
    }

    private function cashierSubscription(Venue $venue, string $price, string $status = 'active', $endsAt = null): Subscription
    {
        return Subscription::create([
            'venue_id' => $venue->id,
            'type' => 'default',
            'stripe_id' => 'sub_'.Str::random(24),
            'stripe_status' => $status,
            'stripe_price' => $price,
            'quantity' => 1,
            'trial_ends_at' => $status === 'trialing' ? now()->addDays(7) : null,
            'ends_at' => $endsAt,
        ]);
    }
}
