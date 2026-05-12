@extends('layouts.app', ['title' => 'Billing', 'venue' => $venue])

@section('content')
<section class="hero compact">
    <div class="shell">
        <div class="eyebrow">Billing</div>
        <h1>Subscription</h1>
        <p>Manage the Resora OS plan, trial and Stripe billing details for {{ $venue->name }}.</p>
    </div>
</section>

<section class="shell billing-suite">
    @if (request('success'))
        <div class="panel success"><p style="margin: 0;">{{ $syncNotice ?? 'Checkout complete. Stripe will confirm your subscription shortly.' }}</p></div>
    @endif
    @if (request('cancelled'))
        <div class="panel notice"><p style="margin: 0;">Checkout was cancelled. You can choose a plan whenever you are ready.</p></div>
    @endif
    @if (request('access') === 'expired')
        <div class="panel errors"><p style="margin: 0;">Your subscription needs attention. Billing remains available so you can restore access.</p></div>
    @endif
    @if (session('status'))
        <div class="panel success"><p style="margin: 0;">{{ session('status') }}</p></div>
    @endif

    <div class="billing-status-grid">
        <article class="panel billing-status-card {{ $status['tone'] }}">
            <span>Current status</span>
            <strong>{{ $status['label'] }}</strong>
            <p>{{ $status['message'] }}</p>
            @if ($status['trial_ends_at'])
                <small>Trial ends {{ $status['trial_ends_at']->format('j M Y') }}</small>
            @endif
        </article>

        <article class="panel billing-status-card">
            <span>Current plan</span>
            <strong>{{ $currentPlan['name'] ?? 'Starter' }}</strong>
            <p>
                @if ($subscription)
                    £{{ $currentPlan['price'] ?? 0 }} per month, billed securely through Stripe.
                @elseif ($trialEligible)
                    No paid plan is active yet. Choose a plan when you are ready to subscribe.
                @else
                    Your workspace is using its included trial access.
                @endif
            </p>
            @if (! empty($currentPlan['feature_list']))
                <small>{{ $currentPlan['feature_list'][0] }}</small>
            @endif
            @if ($subscription?->onGracePeriod())
                <form method="post" action="{{ route('admin.billing.resume') }}">
                    @csrf
                    <button class="primary" type="submit">Resume subscription</button>
                </form>
            @endif
        </article>

        <article class="panel billing-status-card">
            <span>Billing portal</span>
            <strong>Invoices & payment</strong>
            <p>Update payment details, view invoices and manage cancellation directly in Stripe.</p>
            @if ($venue->hasStripeId())
                <form method="post" action="{{ route('admin.billing.portal') }}">
                    @csrf
                    <button class="subtle" type="submit">Manage billing</button>
                </form>
            @else
                <small>Available after checkout creates a Stripe customer.</small>
            @endif
        </article>
    </div>

    <div class="billing-plan-grid">
        @foreach ($plans as $plan)
            @php($isCurrent = ($currentPlan['slug'] ?? null) === $plan['slug'])
            <article class="panel billing-plan-card plan-{{ $plan['slug'] }} {{ $plan['recommended'] ? 'recommended' : '' }}">
                @if ($plan['recommended'])
                    <span class="badge">Recommended</span>
                @endif
                <h2>{{ $plan['name'] }}</h2>
                <div class="billing-price">£{{ $plan['price'] }}<span>/mo</span></div>
                <ul>
                    @foreach ($plan['feature_list'] as $feature)
                        <li>{{ $feature }}</li>
                    @endforeach
                </ul>

                @if (! $plan['stripe_price_id'])
                    <button type="button" disabled>Price ID missing</button>
                @elseif (! $subscription || $subscription->ended())
                    <form method="post" action="{{ route('admin.billing.checkout', $plan['slug']) }}">
                        @csrf
                        <button class="primary" type="submit">{{ $trialEligible ? 'Start '.$trialDays.' day trial' : 'Subscribe' }}</button>
                    </form>
                @elseif ($isCurrent)
                    <button type="button" disabled>Current plan</button>
                @else
                    <form method="post" action="{{ route('admin.billing.swap', $plan['slug']) }}">
                        @csrf
                        <button class="subtle" type="submit">Change to {{ $plan['name'] }}</button>
                    </form>
                @endif
            </article>
        @endforeach
    </div>
</section>
@endsection
