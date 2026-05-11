@extends('layouts.app', ['title' => 'Booking confirmed', 'venue' => $booking->venue])

@section('content')
<section class="hero">
    <div class="shell">
        <div class="eyebrow">Booking confirmed</div>
        <h1>We’ll see you on {{ $booking->starts_at->format('l j F') }}</h1>
        <p>Your reference is <strong>{{ $booking->booking_reference }}</strong>. We have sent this to the diary for the team.</p>
    </div>
</section>

<section class="shell grid booking-grid" style="padding-bottom: 48px;">
    <div class="panel">
        <h2>Booking details</h2>
        <p><strong>{{ $booking->customer->full_name }}</strong></p>
        <p>{{ $booking->party_size }} guests for {{ $booking->service->name }} at {{ $booking->starts_at->format('H:i') }}</p>
        <p>{{ $booking->customer->email }} · {{ $booking->customer->phone }}</p>
        @if ($booking->special_requests)
            <p>{{ $booking->special_requests }}</p>
        @endif
        <div class="actions">
            <a class="button primary" href="{{ route('bookings.manage.show', ['booking' => $booking, 'token' => $booking->customer_manage_token]) }}">Manage booking</a>
            <a class="button subtle" href="{{ route('bookings.create') }}">Make another booking</a>
        </div>
    </div>
    <div class="panel">
        <h2>Restaurant policy</h2>
        <p>{{ $booking->venue->cancellation_policy }}</p>
        <p class="muted">Online changes and cancellations close {{ $booking->venue->cancellation_notice_hours }} hours before arrival.</p>
        <p class="muted">Allocated table: {{ $booking->tables->pluck('name')->join(', ') ?: 'To be assigned' }}</p>
    </div>
</section>
@endsection
