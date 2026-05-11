@extends('layouts.app', ['title' => 'Bookings diary', 'venue' => $venue])

@section('content')
<section class="hero compact">
    <div class="shell">
        <div class="eyebrow">Admin diary</div>
        <h1>{{ $view === 'week' ? $periodStart->format('j M').' to '.$periodEnd->format('j M') : $date->format('l j F') }}</h1>
        <p>{{ $view === 'week' ? 'Weekly service overview for booking operations.' : 'Daily timeline for service flow, tables and guest status.' }}</p>
        <div class="actions">
            <a class="button primary" href="{{ route('admin.bookings.create', ['date' => $date->toDateString()]) }}">Add booking</a>
            <a class="button subtle" href="{{ route('admin.diary', ['date' => today($venue->timezone)->toDateString(), 'view' => $view, 'service_id' => $serviceId]) }}">Today</a>
        </div>
    </div>
</section>

<section class="shell" style="padding-bottom: 48px;">
    @if (session('status'))
        <div class="panel success" style="margin-bottom: 14px;"><p style="margin: 0;">{{ session('status') }}</p></div>
    @endif

    <form method="get" action="{{ route('admin.diary') }}" class="panel diary-toolbar">
        <div class="form-grid">
            <div class="field">
                <label for="date">Diary date</label>
                <input id="date" type="date" name="date" value="{{ $date->toDateString() }}">
            </div>
            <div class="field">
                <label for="service_id">Service</label>
                <select id="service_id" name="service_id">
                    <option value="">All services</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" @selected($serviceId === $service->id)>{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" name="view" value="{{ $view }}">
        </div>
        <div class="actions">
            <div class="view-switch">
                <a class="button {{ $view === 'day' ? 'primary' : 'subtle' }}" href="{{ route('admin.diary', ['date' => $date->toDateString(), 'view' => 'day', 'service_id' => $serviceId]) }}">Day</a>
                <a class="button {{ $view === 'week' ? 'primary' : 'subtle' }}" href="{{ route('admin.diary', ['date' => $date->toDateString(), 'view' => 'week', 'service_id' => $serviceId]) }}">Week</a>
            </div>
            <button class="primary" type="submit">Apply</button>
        </div>
    </form>

    <div class="week-strip">
        @foreach ($days as $day)
            @php($dayBookings = $bookingsByDay->get($day->toDateString(), collect()))
            <a class="day-tile {{ $day->isSameDay($date) ? 'active' : '' }}" href="{{ route('admin.diary', ['date' => $day->toDateString(), 'view' => 'day', 'service_id' => $serviceId]) }}">
                <span>{{ $day->format('D') }}</span>
                <strong>{{ $day->format('j') }}</strong>
                <div class="day-count">{{ $dayBookings->count() }} bookings · {{ $dayBookings->sum('party_size') }} covers</div>
            </a>
        @endforeach
    </div>

    <div class="metric-row">
        <div class="metric"><span>Bookings</span><strong>{{ $bookings->count() }}</strong></div>
        <div class="metric"><span>Covers</span><strong>{{ $bookings->sum('party_size') }}</strong></div>
        <div class="metric"><span>Confirmed</span><strong>{{ $statusCounts->get('confirmed', 0) }}</strong></div>
        <div class="metric"><span>Seated</span><strong>{{ $statusCounts->get('seated', 0) }}</strong></div>
    </div>

    <div class="grid booking-grid">
        <div class="panel">
            <div class="timeline-day">
                <h2>{{ $view === 'week' ? 'Week timeline' : 'Day timeline' }}</h2>
                @if ($serviceId)
                    <span class="badge">{{ $services->firstWhere('id', $serviceId)?->name }}</span>
                @else
                    <span class="badge">All services</span>
                @endif
            </div>

            <div class="timeline-section">
                @forelse ($bookingsByDay as $day => $dayBookings)
                    @if ($view === 'week')
                        <div class="timeline-day">
                            <h3>{{ \Illuminate\Support\Carbon::parse($day, $venue->timezone)->format('l j F') }}</h3>
                            <span class="badge">{{ $dayBookings->count() }} bookings</span>
                        </div>
                    @endif

                    <div class="diary">
                        @foreach ($dayBookings as $booking)
                            <article class="booking-card">
                                <div class="booking-time">
                                    <strong>{{ $booking->starts_at->format('H:i') }}</strong>
                                    <span class="muted">{{ $booking->ends_at->format('H:i') }}</span>
                                    <span class="badge">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                                </div>
                                <div>
                                    <h3>{{ $booking->customer->full_name }} · {{ $booking->party_size }} guests</h3>
                                    <div class="booking-meta">
                                        <span class="badge">{{ $booking->service->name }}</span>
                                        <span class="badge">{{ $booking->booking_reference }}</span>
                                        <span class="badge">{{ ucfirst($booking->source) }}</span>
                                        @foreach ($booking->tables as $table)
                                            <span class="badge">{{ $table->name }} · {{ $table->diningArea->name }}</span>
                                        @endforeach
                                    </div>
                                    @if ($booking->special_requests)
                                        <p style="margin-bottom: 0;">{{ $booking->special_requests }}</p>
                                    @endif
                                </div>
                                <form method="post" action="{{ route('admin.bookings.status.update', $booking) }}">
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
                            </article>
                        @endforeach
                    </div>
                @empty
                    <div class="empty-state">
                        <strong>No bookings found.</strong>
                        <p style="margin: 0;">Try another date, switch service filters, or add a manual booking.</p>
                        <a class="button primary" href="{{ route('admin.bookings.create', ['date' => $date->toDateString()]) }}">Add booking</a>
                    </div>
                @endforelse
            </div>
        </div>

        <aside class="grid">
            <div class="panel">
                <h2>Services</h2>
                <div class="staff-list">
                    @foreach ($services as $service)
                        <a class="staff-card" href="{{ route('admin.diary', ['date' => $date->toDateString(), 'view' => $view, 'service_id' => $service->id]) }}" style="text-decoration: none;">
                            <div>
                                <h3>{{ $service->name }}</h3>
                                <p style="margin: 0;">{{ substr($service->starts_at, 0, 5) }} to {{ substr($service->ends_at, 0, 5) }} · {{ $service->default_duration_minutes }} mins</p>
                            </div>
                            <span class="badge">{{ $bookings->where('service_id', $service->id)->count() }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="panel">
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
            </div>
        </aside>
    </div>
</section>
@endsection
