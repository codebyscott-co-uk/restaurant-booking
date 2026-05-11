@extends('layouts.app', ['title' => 'Manage booking', 'venue' => $venue])

@section('content')
<section class="hero compact">
    <div class="shell">
        <div class="eyebrow">Manage booking</div>
        <h1>{{ $booking->booking_reference }}</h1>
        <p>{{ $booking->starts_at->format('l j F Y') }} at {{ $booking->starts_at->format('H:i') }} for {{ $booking->party_size }} guests.</p>
        <div class="actions">
            @if ($booking->canCustomerCancel())
                <a class="button primary" href="{{ route('bookings.manage.edit', ['booking' => $booking, 'token' => $booking->customer_manage_token]) }}">Modify booking</a>
                <form method="post" action="{{ route('bookings.manage.cancel', ['booking' => $booking, 'token' => $booking->customer_manage_token]) }}" data-confirm="Cancel this booking?">
                    @csrf
                    @method('patch')
                    <button class="danger" type="submit">Cancel booking</button>
                </form>
            @else
                <span class="badge">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
            @endif
        </div>
    </div>
</section>

<section class="shell grid booking-grid" style="padding-bottom: 48px;">
    <div class="panel">
        @if (session('status'))
            <div class="panel success" style="margin-bottom: 14px;"><p style="margin: 0;">{{ session('status') }}</p></div>
        @endif

        @if ($errors->any())
            <div class="panel errors" style="margin-bottom: 14px;">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0 0 4px;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <h2>Booking details</h2>
        <p><strong>{{ $booking->customer->full_name }}</strong></p>
        <p>{{ $booking->service->name }} · {{ $booking->party_size }} guests · {{ $booking->starts_at->format('D j M Y H:i') }}</p>
        <p>{{ $booking->customer->email }} · {{ $booking->customer->phone }}</p>
        @if ($booking->special_requests)
            <p>{{ $booking->special_requests }}</p>
        @endif
        <div class="table-list">
            <span class="badge">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
            <span class="badge">Reference {{ $booking->booking_reference }}</span>
        </div>
    </div>

    <aside class="panel">
        <h2>Changes and cancellation</h2>
        <p>{{ $booking->venue->cancellation_policy }}</p>
        <p class="muted">Online changes and cancellations close {{ $booking->venue->cancellation_notice_hours }} hours before arrival.</p>
        @unless ($booking->canCustomerCancel())
            <div class="notice">
                <strong>Online management is closed.</strong>
                <p style="margin-bottom: 0;">Please contact {{ $booking->venue->phone ?: $booking->venue->contact_email }} if you need help.</p>
            </div>
        @endunless
    </aside>
</section>
@endsection
