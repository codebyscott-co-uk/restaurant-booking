<x-app-layout>
    <div class="container">
        <div class="panel">
            <h1>Billing & Subscription</h1>

            @if ($success)
                <div class="alert alert-success">
                    Your subscription was set up successfully!
                </div>
            @endif

            @if ($cancelled)
                <div class="alert alert-info">
                    You cancelled the checkout. Feel free to try again when ready.
                </div>
            @endif

            <div style="margin-top: 24px;">
                <h2>Current Subscription</h2>

                @if ($venue->stripe_id)
                    <p><strong>Stripe Customer ID:</strong> {{ $venue->stripe_id }}</p>
                    <p><strong>Status:</strong> <span style="font-weight: bold; color: #0f766e;">{{ $venue->stripe_status ?? 'active' }}</span></p>

                    @if ($venue->pm_last_four)
                        <p><strong>Payment Method:</strong> {{ $venue->pm_type ?? 'card' }} ending in {{ $venue->pm_last_four }}</p>
                    @endif

                    @if ($venue->stripe_current_period_end)
                        <p><strong>Renews on:</strong> {{ $venue->stripe_current_period_end->toFormattedDateString() }}</p>
                    @endif
                @else
                    <p style="color: #666;">No active subscription yet.</p>
                @endif
            </div>

            <div style="margin-top: 24px;">
                @if (!$venue->stripe_id || $venue->stripe_status !== 'active')
                    <form method="post" action="{{ route('admin.billing.checkout') }}">
                        @csrf
                        <button type="submit" class="button primary">
                            {{ $venue->stripe_id ? 'Reactivate Subscription' : 'Start Subscription' }}
                        </button>
                    </form>
                @else
                    <p style="color: #666;">Manage your subscription and payment methods in the dashboard above.</p>
                @endif
            </div>

            @if ($errors->any())
                <div style="margin-top: 24px; padding: 12px; background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 4px;">
                    <strong style="color: #991b1b;">Error:</strong>
                    <ul style="margin: 8px 0 0 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li style="color: #991b1b;">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
