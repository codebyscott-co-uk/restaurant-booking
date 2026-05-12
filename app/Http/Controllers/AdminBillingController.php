<?php

namespace App\Http\Controllers;

use App\Services\Billing\BillingPlans;
use App\Services\Billing\FeatureGate;
use App\Services\Billing\StripeBillingGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AdminBillingController extends Controller
{
    public function index(Request $request, BillingPlans $plans, FeatureGate $features, StripeBillingGateway $gateway): View
    {
        $venue = $this->currentVenue($request)->load('activeSubscription');
        $syncNotice = null;

        if ($request->boolean('success') && $request->filled('session_id')) {
            try {
                $syncNotice = $gateway->syncCheckoutSession($venue, $request->string('session_id')->toString())
                    ? 'Your subscription has been activated.'
                    : 'Checkout completed. Stripe will confirm your subscription shortly.';

                $venue = $venue->fresh()->load('activeSubscription');
            } catch (Throwable $exception) {
                report($exception);

                $syncNotice = 'Checkout completed. Waiting for Stripe to confirm your subscription.';
            }
        }

        $subscription = $venue->subscription('default');
        $currentPlan = $features->currentPlan($venue);

        return view('admin.billing.index', [
            'venue' => $venue,
            'plans' => $plans->all(),
            'trialDays' => $plans->trialDays(),
            'subscription' => $subscription,
            'currentPlan' => $currentPlan,
            'status' => $this->status($subscription, $venue),
            'trialEligible' => $this->trialEligible($venue),
            'syncNotice' => $syncNotice,
        ]);
    }

    public function checkout(Request $request, string $plan, BillingPlans $plans, StripeBillingGateway $gateway): RedirectResponse
    {
        $venue = $this->currentVenue($request);
        $planConfig = $plans->get($plan);

        abort_unless($planConfig['stripe_price_id'], 422, 'This plan is missing a Stripe price ID.');

        $subscription = $venue->subscription('default');

        if ($subscription && ! $subscription->ended()) {
            return redirect()
                ->route('admin.billing.index')
                ->with('status', 'You already have a subscription. Use change plan instead.');
        }

        return $gateway->checkout($venue, $planConfig, $this->trialEligible($venue), $plans->trialDays());
    }

    public function swap(Request $request, string $plan, BillingPlans $plans): RedirectResponse
    {
        $venue = $this->currentVenue($request);
        $planConfig = $plans->get($plan);

        abort_unless($planConfig['stripe_price_id'], 422, 'This plan is missing a Stripe price ID.');

        $subscription = $venue->subscription('default');
        abort_unless($subscription && $subscription->valid(), 404);

        if ($subscription->stripe_price === $planConfig['stripe_price_id']) {
            return back()->with('status', 'You are already on this plan.');
        }

        $subscription->swap($planConfig['stripe_price_id']);

        return redirect()->route('admin.billing.index')->with('status', 'Plan updated.');
    }

    public function resume(Request $request): RedirectResponse
    {
        $subscription = $this->currentVenue($request)->subscription('default');

        abort_unless($subscription && $subscription->onGracePeriod(), 404);

        $subscription->resume();

        return redirect()->route('admin.billing.index')->with('status', 'Subscription resumed.');
    }

    public function portal(Request $request, StripeBillingGateway $gateway): RedirectResponse
    {
        $venue = $this->currentVenue($request);

        abort_unless($venue->hasStripeId(), 404);

        return $gateway->portal($venue);
    }

    private function trialEligible($venue): bool
    {
        return ! $venue->subscriptions()->exists();
    }

    private function status($subscription, $venue): array
    {
        if (! $subscription) {
            $trial = $venue->activeSubscription?->trial_ends_at;

            return [
                'label' => $trial?->isFuture() ? 'Trialing' : 'No subscription',
                'tone' => $trial?->isFuture() ? 'info' : 'warning',
                'message' => $trial?->isFuture()
                    ? 'Your Resora OS workspace is in its local trial period.'
                    : 'Choose a plan to activate hosted billing for this venue.',
                'trial_ends_at' => $trial,
            ];
        }

        if ($subscription->onGracePeriod()) {
            return [
                'label' => 'Cancelling',
                'tone' => 'warning',
                'message' => 'Your subscription is cancelled but remains active until the end of the billing period.',
                'trial_ends_at' => $subscription->trial_ends_at,
            ];
        }

        return match ($subscription->stripe_status) {
            'trialing' => [
                'label' => 'Trialing',
                'tone' => 'info',
                'message' => 'Your Stripe trial is active.',
                'trial_ends_at' => $subscription->trial_ends_at,
            ],
            'active' => [
                'label' => 'Active',
                'tone' => 'success',
                'message' => 'Your subscription is active.',
                'trial_ends_at' => $subscription->trial_ends_at,
            ],
            'past_due' => [
                'label' => 'Past due',
                'tone' => 'danger',
                'message' => 'Payment is past due. Update your payment method in the billing portal.',
                'trial_ends_at' => $subscription->trial_ends_at,
            ],
            'incomplete' => [
                'label' => 'Incomplete',
                'tone' => 'warning',
                'message' => 'Stripe needs payment confirmation before the subscription becomes active.',
                'trial_ends_at' => $subscription->trial_ends_at,
            ],
            'canceled' => [
                'label' => 'Cancelled',
                'tone' => 'danger',
                'message' => 'This subscription has ended. Choose a plan to subscribe again.',
                'trial_ends_at' => $subscription->trial_ends_at,
            ],
            'unpaid', 'incomplete_expired' => [
                'label' => 'Expired',
                'tone' => 'danger',
                'message' => 'Billing access has expired. Choose a plan or update billing details.',
                'trial_ends_at' => $subscription->trial_ends_at,
            ],
            default => [
                'label' => ucfirst(str_replace('_', ' ', $subscription->stripe_status)),
                'tone' => 'warning',
                'message' => 'Review your billing state in Stripe.',
                'trial_ends_at' => $subscription->trial_ends_at,
            ],
        };
    }
}
