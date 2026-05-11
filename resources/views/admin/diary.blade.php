@extends('layouts.app', ['title' => 'Bookings diary', 'venue' => $venue])

@section('content')
<section class="hero">
    <div class="shell">
        <div class="eyebrow">Admin diary</div>
        <h1>{{ $date->format('l j F') }}</h1>
        @if (session('status'))
            <div class="panel success" style="max-width: 460px; margin-bottom: 14px;"><p style="margin: 0;">{{ session('status') }}</p></div>
        @endif
        <form method="get" action="{{ route('admin.diary') }}" class="panel" style="max-width: 460px;">
            <div class="form-grid">
                <div class="field">
                    <label for="date">Diary date</label>
                    <input id="date" type="date" name="date" value="{{ $date->toDateString() }}">
                </div>
                <button class="primary" type="submit">Open diary</button>
            </div>
        </form>
        <a class="button primary" href="{{ route('admin.bookings.create', ['date' => $date->toDateString()]) }}" style="margin-top: 12px;">Add booking</a>
    </div>
</section>

<section class="shell" style="padding-bottom: 48px;">
    <div class="metric-row">
        <div class="metric"><span class="muted">Bookings</span><strong>{{ $bookings->count() }}</strong></div>
        <div class="metric"><span class="muted">Covers</span><strong>{{ $bookings->sum('party_size') }}</strong></div>
        <div class="metric"><span class="muted">Confirmed</span><strong>{{ $statusCounts->get('confirmed', 0) }}</strong></div>
        <div class="metric"><span class="muted">Seated</span><strong>{{ $statusCounts->get('seated', 0) }}</strong></div>
    </div>

    <div class="grid booking-grid">
        <div class="panel">
            <h2>Timeline</h2>
            <div class="diary">
                @forelse ($bookings as $booking)
                    <article class="booking-card">
                        <div>
                            <strong>{{ $booking->starts_at->format('H:i') }}</strong>
                            <p style="margin: 4px 0 0;">{{ $booking->ends_at->format('H:i') }}</p>
                        </div>
                        <div>
                            <h3>{{ $booking->customer->full_name }} · {{ $booking->party_size }} guests</h3>
                            <p style="margin: 0;">{{ $booking->service->name }} · {{ $booking->booking_reference }} · {{ $booking->source }}</p>
                            @if ($booking->special_requests)
                                <p style="margin-bottom: 0;">{{ $booking->special_requests }}</p>
                            @endif
                            <div class="table-list">
                                @foreach ($booking->tables as $table)
                                    <span class="badge">{{ $table->name }} · {{ $table->diningArea->name }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <span class="badge">{{ str_replace('_', ' ', $booking->status) }}</span>
                            <form method="post" action="{{ route('admin.bookings.status.update', $booking) }}" style="margin-top: 10px;">
                                @csrf
                                @method('patch')
                                <div class="field">
                                    <label for="status-{{ $booking->id }}">Status</label>
                                    <select id="status-{{ $booking->id }}" name="status" onchange="this.form.submit()">
                                        @foreach (\App\Models\Booking::STATUSES as $status)
                                            <option value="{{ $status }}" @selected($booking->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="empty-state">
                        <strong>No bookings yet.</strong>
                        <p style="margin: 0;">New web bookings will appear here as soon as guests confirm, or staff can add one manually.</p>
                        <a class="button primary" href="{{ route('admin.bookings.create', ['date' => $date->toDateString()]) }}">Add booking</a>
                    </div>
                @endforelse
            </div>
        </div>

        <aside class="panel">
            <h2>Setup</h2>
            @foreach ($services as $service)
                <div style="margin-bottom: 12px;">
                    <h3>{{ $service->name }}</h3>
                    <p style="margin: 0;">{{ substr($service->starts_at, 0, 5) }} to {{ substr($service->ends_at, 0, 5) }} · {{ $service->default_duration_minutes }} mins</p>
                </div>
            @endforeach
            <h2>Areas</h2>
            @foreach ($venue->diningAreas as $area)
                <div style="margin-bottom: 14px;">
                    <h3>{{ $area->name }}</h3>
                    <div class="table-list">
                        @foreach ($area->tables as $table)
                            <span class="badge">{{ $table->name }} · {{ $table->min_covers }}-{{ $table->max_covers }}</span>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </aside>
    </div>
</section>
@endsection
