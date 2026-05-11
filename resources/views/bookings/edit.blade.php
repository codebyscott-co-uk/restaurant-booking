@extends('layouts.app', ['title' => 'Modify booking', 'venue' => $venue])

@section('content')
<section class="hero">
    <div class="shell">
        <div class="eyebrow">Modify booking</div>
        <h1>Change {{ $booking->booking_reference }}</h1>
        <p>Choose a new date, time or party size. Online changes close {{ $venue->cancellation_notice_hours }} hours before arrival.</p>
    </div>
</section>

<section class="shell grid booking-grid" style="padding-bottom: 48px;">
    <form class="panel" method="get" action="{{ route('bookings.manage.edit', ['booking' => $booking, 'token' => $booking->customer_manage_token]) }}">
        <h2>Find a new time</h2>
        <div class="form-grid">
            <div class="field">
                <label for="party_size">Party size</label>
                <input id="party_size" name="party_size" type="number" min="1" max="{{ $venue->maximum_party_size }}" value="{{ $partySize }}">
            </div>
            <div class="field">
                <label for="date">Date</label>
                <input id="date" name="date" type="date" value="{{ $selectedDate->toDateString() }}">
            </div>
            <div class="field full">
                <label for="service_id">Service</label>
                <select id="service_id" name="service_id">
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" @selected($selectedService && $selectedService->id === $service->id)>
                            {{ $service->name }} · {{ substr($service->starts_at, 0, 5) }} to {{ substr($service->ends_at, 0, 5) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button class="primary full" type="submit">Show available times</button>
        </div>
    </form>

    <form class="panel" method="post" action="{{ route('bookings.manage.update', ['booking' => $booking, 'token' => $booking->customer_manage_token]) }}">
        @csrf
        @method('put')
        <h2>Confirm changes</h2>

        @if ($errors->any())
            <div class="panel errors" style="margin-bottom: 14px;">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0 0 4px;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <input type="hidden" name="service_id" value="{{ $selectedService?->id }}">
        <input type="hidden" name="party_size" value="{{ $partySize }}">
        <input type="hidden" name="date" value="{{ $selectedDate->toDateString() }}">

        <h3>{{ $partySize }} guests · {{ $selectedDate->format('D j M') }} · {{ $selectedService?->name }}</h3>

        @if ($slots->isEmpty())
            <div class="notice">
                <strong>No available times for this selection.</strong>
                <p style="margin-bottom: 0;">Try a different date, service or party size.</p>
            </div>
        @else
            <div class="slots" aria-label="Available times">
                @foreach ($slots as $slot)
                    <label class="slot">
                        <input type="radio" name="time" value="{{ $slot->format('H:i') }}" @checked(old('time') === $slot->format('H:i') || ($booking->starts_at->format('H:i') === $slot->format('H:i') && ! old('time')))>
                        <span>{{ $slot->format('H:i') }}</span>
                    </label>
                @endforeach
            </div>
        @endif

        <div class="form-grid" style="margin-top: 18px;">
            <div class="field">
                <label for="first_name">First name</label>
                <input id="first_name" name="first_name" value="{{ old('first_name', $booking->customer->first_name) }}" autocomplete="given-name" required>
            </div>
            <div class="field">
                <label for="last_name">Last name</label>
                <input id="last_name" name="last_name" value="{{ old('last_name', $booking->customer->last_name) }}" autocomplete="family-name" required>
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $booking->customer->email) }}" autocomplete="email" required>
            </div>
            <div class="field">
                <label for="phone">Phone</label>
                <input id="phone" name="phone" value="{{ old('phone', $booking->customer->phone) }}" autocomplete="tel" required>
            </div>
            <div class="field full">
                <label for="special_requests">Special requests</label>
                <textarea id="special_requests" name="special_requests">{{ old('special_requests', $booking->special_requests) }}</textarea>
            </div>
            <button class="primary full" type="submit" @disabled($slots->isEmpty())>Update booking</button>
        </div>
    </form>
</section>
@endsection
