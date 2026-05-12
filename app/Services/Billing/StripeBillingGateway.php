<?php

namespace App\Services\Billing;

use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Laravel\Cashier\Cashier;

class StripeBillingGateway
{
    public function checkout(Venue $venue, array $plan, bool $trialEligible, int $trialDays): RedirectResponse
    {
        $builder = $venue
            ->newSubscription('default', $plan['stripe_price_id'])
            ->withMetadata([
                'venue_id' => (string) $venue->id,
                'plan' => $plan['slug'],
            ]);

        if ($trialEligible && $trialDays > 0) {
            $builder->trialDays($trialDays);
        }

        return $builder->checkout([
            'success_url' => route('admin.billing.index', ['success' => 1]).'&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('admin.billing.index', ['cancelled' => 1]),
            'allow_promotion_codes' => true,
        ])->redirect();
    }

    public function portal(Venue $venue): RedirectResponse
    {
        return $venue->redirectToBillingPortal(route('admin.billing.index'));
    }

    public function syncCheckoutSession(Venue $venue, string $sessionId): bool
    {
        $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId, []);

        if (($session->mode ?? null) !== 'subscription' || ! ($session->subscription ?? null)) {
            return false;
        }

        if ($venue->stripe_id && ($session->customer ?? null) !== $venue->stripe_id) {
            return false;
        }

        if (! $venue->stripe_id && ($session->customer ?? null)) {
            $venue->forceFill(['stripe_id' => $session->customer])->save();
        }

        $stripeSubscription = Cashier::stripe()->subscriptions->retrieve($session->subscription, [
            'expand' => ['items.data.price'],
        ]);

        $firstItem = $stripeSubscription->items->data[0] ?? null;
        $isSinglePrice = count($stripeSubscription->items->data ?? []) === 1;

        $subscription = $venue->subscriptions()->updateOrCreate([
            'stripe_id' => $stripeSubscription->id,
        ], [
            'type' => $stripeSubscription->metadata->type ?? $stripeSubscription->metadata->name ?? 'default',
            'stripe_status' => $stripeSubscription->status,
            'stripe_price' => $isSinglePrice ? $firstItem?->price?->id : null,
            'quantity' => $isSinglePrice ? ($firstItem->quantity ?? null) : null,
            'trial_ends_at' => $stripeSubscription->trial_end
                ? Carbon::createFromTimestamp($stripeSubscription->trial_end)
                : null,
            'ends_at' => $stripeSubscription->cancel_at
                ? Carbon::createFromTimestamp($stripeSubscription->cancel_at)
                : null,
        ]);

        foreach ($stripeSubscription->items->data ?? [] as $item) {
            $subscription->items()->updateOrCreate([
                'stripe_id' => $item->id,
            ], [
                'stripe_product' => $item->price->product,
                'stripe_price' => $item->price->id,
                'quantity' => $item->quantity ?? null,
            ]);
        }

        return true;
    }
}
