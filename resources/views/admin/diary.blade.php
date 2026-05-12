@extends('layouts.app', ['title' => 'Bookings diary', 'venue' => $venue])

@section('content')
@php
    $statusMeta = [
        'pending' => ['label' => 'Pending', 'class' => 'amber'],
        'confirmed' => ['label' => 'Confirmed', 'class' => 'violet'],
        'seated' => ['label' => 'Seated', 'class' => 'cyan'],
        'completed' => ['label' => 'Completed', 'class' => 'green'],
        'cancelled' => ['label' => 'Cancelled', 'class' => 'red'],
        'no_show' => ['label' => 'No-show', 'class' => 'slate'],
    ];
    $sourceLabels = ['web' => 'Online', 'phone' => 'Phone', 'walk_in' => 'Walk-in', 'staff' => 'Staff'];
    $activeCovers = $bookings->whereNotIn('status', ['cancelled', 'no_show'])->sum('party_size');
    $query = fn (array $merge = []) => array_filter(array_merge(request()->query(), $merge), fn ($value) => $value !== null && $value !== '');
@endphp

<section class="hero compact bookings-hero">
    <div class="shell">
        <div class="dashboard-hero-grid">
            <div>
                <div class="eyebrow">Bookings diary</div>
                <h1>{{ $view === 'week' ? $periodStart->format('j M').' to '.$periodEnd->format('j M') : $date->format('l j F') }}</h1>
                <p>Fast service-floor visibility across guests, covers, tables, requests and booking status.</p>
            </div>
            <div class="insight-card gradient-violet">
                <div>
                    <span>Selected period</span>
                    <strong>{{ $bookings->count() }}</strong>
                    <p>{{ $activeCovers }} active covers · {{ $statusCounts->get('confirmed', 0) }} confirmed</p>
                </div>
                <div class="orbital-chart" style="--value: {{ min(100, $activeCovers * 4) }};">
                    <span>{{ $activeCovers }}</span>
                </div>
            </div>
        </div>
        <div class="actions bookings-hero-actions">
            <a class="button primary" href="{{ route('admin.bookings.create', ['date' => $date->toDateString()]) }}">Add booking</a>
            <a class="button subtle" href="{{ route('admin.diary', $query(['date' => today($venue->timezone)->toDateString()])) }}">Today</a>
            <a class="button subtle" href="{{ route('admin.diary', $query(['date' => today($venue->timezone)->addDay()->toDateString()])) }}">Tomorrow</a>
            <a class="button" href="{{ route('admin.diary', $query(['date' => $date->copy()->subDay()->toDateString()])) }}">Previous</a>
            <a class="button" href="{{ route('admin.diary', $query(['date' => $date->copy()->addDay()->toDateString()])) }}">Next</a>
        </div>
    </div>
</section>

<section class="shell bookings-suite">
    @if (session('status'))
        <div class="panel success"><p style="margin: 0;">{{ session('status') }}</p></div>
    @endif

    <form method="get" action="{{ route('admin.diary') }}" class="panel booking-filter-panel">
        <div class="field">
            <label for="date">Date</label>
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
        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">All statuses</option>
                @foreach ($statusMeta as $key => $meta)
                    <option value="{{ $key }}" @selected($status === $key)>{{ $meta['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="search">Search</label>
            <input id="search" name="search" value="{{ $search }}" placeholder="Guest, email, phone or ref">
        </div>
        <input type="hidden" name="view" value="{{ $view }}">
        <input type="hidden" name="display" value="{{ $display }}">
        <div class="actions">
            <button class="primary" type="submit">Apply</button>
            <a class="button subtle" href="{{ route('admin.diary', ['date' => $date->toDateString()]) }}">Reset</a>
        </div>
    </form>

    <div class="view-switch booking-view-switch">
        <a class="button {{ $view === 'day' ? 'primary' : 'subtle' }}" href="{{ route('admin.diary', $query(['view' => 'day'])) }}">Day</a>
        <a class="button {{ $view === 'week' ? 'primary' : 'subtle' }}" href="{{ route('admin.diary', $query(['view' => 'week'])) }}">Week</a>
        <a class="button {{ $display === 'timeline' ? 'primary' : 'subtle' }}" href="{{ route('admin.diary', $query(['display' => 'timeline'])) }}">Timeline</a>
        <a class="button {{ $display === 'list' ? 'primary' : 'subtle' }}" href="{{ route('admin.diary', $query(['display' => 'list'])) }}">List</a>
    </div>

    <div class="booking-summary-grid">
        <article class="booking-summary-card violet"><span>Total bookings</span><strong>{{ $bookings->count() }}</strong><small>{{ $view === 'week' ? 'This week' : 'Selected day' }}</small></article>
        <article class="booking-summary-card green"><span>Total covers</span><strong>{{ $activeCovers }}</strong><small>Excludes cancelled/no-show</small></article>
        <article class="booking-summary-card violet"><span>Confirmed</span><strong>{{ $statusCounts->get('confirmed', 0) }}</strong><small>Ready to arrive</small></article>
        <article class="booking-summary-card cyan"><span>Seated</span><strong>{{ $statusCounts->get('seated', 0) }}</strong><small>Currently in service</small></article>
        <article class="booking-summary-card green"><span>Completed</span><strong>{{ $statusCounts->get('completed', 0) }}</strong><small>Finished covers</small></article>
        <article class="booking-summary-card red"><span>Cancelled</span><strong>{{ $statusCounts->get('cancelled', 0) }}</strong><small>Removed from capacity</small></article>
        <article class="booking-summary-card slate"><span>No-shows</span><strong>{{ $statusCounts->get('no_show', 0) }}</strong><small>Operational watchlist</small></article>
    </div>

    @if ($services->isEmpty())
        <div class="empty-state">
            <strong>No services configured.</strong>
            <p style="margin: 0;">Add lunch, dinner or custom services before taking bookings.</p>
            <a class="button primary" href="{{ route('admin.services.create') }}">Add service</a>
        </div>
    @elseif ($venue->tables->isEmpty())
        <div class="empty-state">
            <strong>No tables configured.</strong>
            <p style="margin: 0;">Add dining areas and tables so bookings can be allocated accurately.</p>
            <a class="button primary" href="{{ route('admin.areas.index') }}">Manage tables</a>
        </div>
    @elseif ($bookings->isEmpty())
        <div class="empty-state">
            <strong>{{ $search || $status || $serviceId ? 'No bookings match these filters.' : 'No bookings for this period.' }}</strong>
            <p style="margin: 0;">Try clearing filters or add a manual phone, walk-in or staff booking.</p>
            <div class="actions">
                <a class="button primary" href="{{ route('admin.bookings.create', ['date' => $date->toDateString()]) }}">Add booking</a>
                <a class="button subtle" href="{{ route('admin.diary', ['date' => $date->toDateString()]) }}">Clear filters</a>
            </div>
        </div>
    @elseif ($display === 'list')
        <div class="panel dashboard-widget">
            <div class="widget-heading">
                <div>
                    <h2>Booking list</h2>
                    <p>Compact operational view for searching, status checks and quick edits.</p>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Guest</th>
                            <th>Contact</th>
                            <th>Covers</th>
                            <th>Service</th>
                            <th>Tables</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Reference</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $booking)
                            <tr>
                                <td>{{ $booking->starts_at->format('D H:i') }}</td>
                                <td>{{ $booking->customer->full_name }}</td>
                                <td>{{ $booking->customer->phone }}<br><small>{{ $booking->customer->email }}</small></td>
                                <td>{{ $booking->party_size }}</td>
                                <td>{{ $booking->service->name }}</td>
                                <td>{{ $booking->tables->map(fn ($table) => $table->name.' · '.$table->diningArea->name)->join(', ') ?: 'Unassigned' }}</td>
                                <td>{{ $sourceLabels[$booking->source] ?? ucfirst(str_replace('_', ' ', $booking->source)) }}</td>
                                <td><span class="badge status-badge {{ $statusMeta[$booking->status]['class'] ?? 'slate' }}">{{ $statusMeta[$booking->status]['label'] ?? $booking->status }}</span></td>
                                <td>{{ $booking->booking_reference }}</td>
                                <td><a class="button subtle" href="#booking-{{ $booking->id }}">Details</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="booking-service-timeline">
            @foreach ($services as $service)
                @php($serviceBookings = $bookingsByService->get($service->id, collect()))
                <section class="panel dashboard-widget service-diary-section">
                    <div class="widget-heading">
                        <div>
                            <h2>{{ $service->name }}</h2>
                            <p>{{ substr($service->starts_at, 0, 5) }} to {{ substr($service->ends_at, 0, 5) }} · {{ $serviceBookings->count() }} bookings · {{ $serviceBookings->whereNotIn('status', ['cancelled', 'no_show'])->sum('party_size') }} covers</p>
                        </div>
                        <a class="button subtle" href="{{ route('admin.bookings.create', ['date' => $date->toDateString(), 'service_id' => $service->id]) }}">Add</a>
                    </div>
                    <div class="diary">
                        @forelse ($serviceBookings as $booking)
                            @include('admin.bookings.partials.card', compact('booking', 'statusMeta', 'sourceLabels'))
                        @empty
                            <div class="empty-state compact">
                                <strong>No {{ strtolower($service->name) }} bookings.</strong>
                                <p style="margin: 0;">This service is clear for the selected filters.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    @endif

    @foreach ($bookings as $booking)
        @include('admin.bookings.partials.drawer', compact('booking', 'statusMeta', 'sourceLabels'))
    @endforeach
</section>
@endsection
