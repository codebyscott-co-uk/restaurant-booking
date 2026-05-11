@extends('layouts.app', ['title' => 'Dashboard', 'venue' => $venue])

@section('content')
<section class="hero compact">
    <div class="shell">
        <div class="eyebrow">Staff dashboard</div>
        <h1>{{ $venue->name }}</h1>
        <p>Today at a glance, quick actions, and setup health for your booking operation.</p>
        <div class="actions">
            <a class="button primary" href="{{ route('admin.bookings.create', ['date' => $today->toDateString()]) }}">Add booking</a>
            <a class="button subtle" href="{{ route('admin.diary', ['date' => $today->toDateString()]) }}">Open diary</a>
        </div>
    </div>
</section>

<section class="shell" style="padding-bottom: 48px;">
    <div class="metric-row">
        <div class="metric"><span>Today bookings</span><strong>{{ $todayBookings->count() }}</strong></div>
        <div class="metric"><span>Today covers</span><strong>{{ $todayBookings->sum('party_size') }}</strong></div>
        <div class="metric"><span>Seated now</span><strong>{{ $statusCounts->get('seated', 0) }}</strong></div>
        <div class="metric"><span>Week covers</span><strong>{{ $weekCovers }}</strong></div>
    </div>

    <div class="grid dashboard-grid">
        <div class="panel">
            <h2>Today’s flow</h2>
            <div class="diary">
                @forelse ($todayBookings as $booking)
                    <article class="booking-card">
                        <div>
                            <strong>{{ $booking->starts_at->format('H:i') }}</strong>
                            <p style="margin: 4px 0 0;">{{ $booking->service->name }}</p>
                        </div>
                        <div>
                            <h3>{{ $booking->customer->full_name }} · {{ $booking->party_size }} guests</h3>
                            <p style="margin: 0;">{{ $booking->booking_reference }} · {{ $booking->source }}</p>
                            <div class="table-list">
                                @foreach ($booking->tables as $table)
                                    <span class="badge">{{ $table->name }}</span>
                                @endforeach
                            </div>
                        </div>
                        <span class="badge">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                    </article>
                @empty
                    <div class="empty-state">
                        <strong>No bookings today.</strong>
                        <p style="margin: 0;">Use the diary to add a phone booking or check another date.</p>
                        <a class="button primary" href="{{ route('admin.bookings.create', ['date' => $today->toDateString()]) }}">Add booking</a>
                    </div>
                @endforelse
            </div>
        </div>

        <aside class="grid">
            <div class="panel">
                <h2>Quick actions</h2>
                <div class="quick-actions">
                    <a class="button" href="{{ route('admin.bookings.create') }}">New booking</a>
                    <a class="button" href="{{ route('admin.availability.index') }}">Edit hours</a>
                    <a class="button" href="{{ route('admin.services.index') }}">Services</a>
                    <a class="button" href="{{ route('admin.areas.index') }}">Tables</a>
                </div>
            </div>

            <div class="panel">
                <h2>Upcoming</h2>
                <div class="staff-list">
                    @forelse ($upcomingBookings as $booking)
                        <article>
                            <h3>{{ $booking->starts_at->format('D j M H:i') }}</h3>
                            <p style="margin: 0;">{{ $booking->customer->full_name }} · {{ $booking->party_size }} guests · {{ $booking->service->name }}</p>
                        </article>
                    @empty
                        <div class="empty-state">
                            <strong>No upcoming bookings.</strong>
                            <p style="margin: 0;">Online and staff-created bookings will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h2>Setup health</h2>
                <div class="table-list">
                    <span class="badge">{{ $venue->services->count() }} services</span>
                    <span class="badge">{{ $venue->diningAreas->count() }} areas</span>
                    <span class="badge">{{ $venue->tables->count() }} tables</span>
                    <span class="badge">{{ $venue->contact_email ? 'Email set' : 'Email missing' }}</span>
                </div>
                <p style="margin-bottom: 0;">Booking window: {{ $venue->maximum_advance_booking_days }} days · Lead time: {{ $venue->minimum_lead_time_minutes }} mins</p>
            </div>
        </aside>
    </div>
</section>
@endsection

