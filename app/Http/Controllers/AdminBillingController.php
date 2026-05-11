<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminBillingController extends Controller
{
    public function index(Request $request): View
    {
        $venue = $this->currentVenue();

        return view('admin.billing.index', [
            'venue' => $venue,
            'stripeKey' => config('services.stripe.key'),
            'priceId' => config('services.stripe.price_id'),
            'success' => $request->boolean('success'),
            'cancelled' => $request->boolean('cancelled'),
        ]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        $venue = $this->currentVenue($request);
        $priceId = config('services.stripe.price_id');

        if (! $priceId) {
            return back()->withErrors(['billing' => 'Stripe price ID is not configured.']);
        }

        // Create a new subscription or redirect to Stripe Checkout
        return $venue->newSubscription('default', $priceId)
            ->returnTo(route('admin.billing.index', ['success' => 1]))
            ->redirectToCheckout();
    }
}
