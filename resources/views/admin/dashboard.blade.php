@extends('layouts.app', ['title' => 'Dashboard', 'venue' => $venue])

@section('content')
@php
    $statusPalette = [
        'confirmed' => ['label' => 'Confirmed', 'class' => 'violet'],
        'seated' => ['label' => 'Seated', 'class' => 'cyan'],
        'completed' => ['label' => 'Completed', 'class' => 'green'],
        'pending' => ['label' => 'Pending', 'class' => 'amber'],
        'cancelled' => ['label' => 'Cancelled', 'class' => 'red'],
        'no_show' => ['label' => 'No show', 'class' => 'slate'],
    ];
    $totalTodayBookings = max(1, $todayBookings->count());
@endphp

<section class="hero compact dashboard-hero">
    <div class="shell">
        <div class="dashboard-hero-grid">
            <div>
                <div class="eyebrow">Staff dashboard</div>
                <h1>{{ $venue->name }}</h1>
                <p>Live booking intelligence, service momentum, and operational health for today’s restaurant floor.</p>
                <div class="actions">
                    <a class="button primary" href="{{ route('admin.bookings.create', ['date' => $today->toDateString()]) }}">Add booking</a>
                    <a class="button subtle" href="{{ route('admin.diary', ['date' => $today->toDateString()]) }}">Open diary</a>
                </div>
            </div>

            <div class="insight-card gradient-violet">
                <div>
                    <span>Today covers</span>
                    <strong>{{ $todayCovers }}</strong>
                    <p>{{ $todayCoverPercent }}% of your configured slot cover guide.</p>
                </div>
                <div class="orbital-chart" style="--value: {{ $todayCoverPercent }};">
                    <span>{{ $todayCoverPercent }}%</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="shell dashboard-suite">
    <div class="dashboard-kpis">
        <article class="kpi-card violet">
            <span>Today bookings</span>
            <strong>{{ $todayBookings->count() }}</strong>
            <small>{{ $statusCounts->get('confirmed', 0) }} confirmed · {{ $statusCounts->get('seated', 0) }} seated</small>
            <div class="sparkline" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
        </article>
        <article class="kpi-card cyan">
            <span>Week covers</span>
            <strong>{{ $weekCovers }}</strong>
            <small>{{ $weekCoverTrend >= 0 ? '+' : '' }}{{ $weekCoverTrend }}% vs previous 7 days</small>
            <div class="sparkline" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
        </article>
        <article class="kpi-card green">
            <span>Table load</span>
            <strong>{{ $tableLoadPercent }}%</strong>
            <small>{{ $todayCovers }} covers across {{ $tableCapacity }} table seats</small>
            <div class="mini-progress"><span style="width: {{ $tableLoadPercent }}%;"></span></div>
        </article>
        <article class="kpi-card amber">
            <span>Deposits this week</span>
            <strong>£{{ number_format((float) $depositTotal, 0) }}</strong>
            <small>{{ $weekBookings->count() }} bookings in the next 7 days</small>
            <div class="sparkline" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
        </article>
    </div>

    <div class="dashboard-mosaic">
        <section class="panel dashboard-widget service-widget">
            <div class="widget-heading">
                <div>
                    <h2>Service performance</h2>
                    <p>Cover demand by service today.</p>
                </div>
                <span class="badge">Live mix</span>
            </div>

            <div class="service-bars">
                @forelse ($serviceMix as $service)
                    @php($width = max(8, min(100, (int) round(($service['covers'] / $maxServiceCovers) * 100))))
                    <div class="service-bar">
                        <div>
                            <strong>{{ $service['name'] }}</strong>
                            <span>{{ $service['bookings'] }} bookings · {{ $service['covers'] }} covers</span>
                        </div>
                        <div class="bar-track"><span style="width: {{ $width }}%;"></span></div>
                    </div>
                @empty
                    <div class="empty-state">
                        <strong>No services configured.</strong>
                        <p style="margin: 0;">Add lunch, dinner, or event services to start tracking demand.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="panel dashboard-widget flow-widget">
            <div class="widget-heading">
                <div>
                    <h2>Cover flow</h2>
                    <p>Expected covers by arrival time.</p>
                </div>
                <span class="badge">{{ $today->format('D j M') }}</span>
            </div>
            <div class="flow-chart">
                @forelse ($hourlyFlow as $slot)
                    @php($height = max(12, min(100, (int) round(($slot['covers'] / $maxHourlyCovers) * 100))))
                    <div class="flow-column" style="--height: {{ $height }}%;">
                        <span>{{ $slot['covers'] }}</span>
                        <i></i>
                        <small>{{ $slot['time'] }}</small>
                    </div>
                @empty
                    <div class="empty-state">
                        <strong>No arrivals today.</strong>
                        <p style="margin: 0;">New online or staff bookings will build this chart automatically.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="panel dashboard-widget experience-widget">
            <div class="widget-heading">
                <div>
                    <h2>Guest experience</h2>
                    <p>Booking reliability across the next 7 days.</p>
                </div>
                <span class="badge">{{ $guestExperienceScore }}%</span>
            </div>
            <div class="experience-meter" style="--value: {{ $guestExperienceScore }};">
                <span>{{ $guestExperienceScore }}</span>
            </div>
            <div class="status-grid">
                @foreach ($statusPalette as $status => $meta)
                    @php($count = $statusCounts->get($status, 0))
                    @php($percent = max(4, min(100, (int) round(($count / $totalTodayBookings) * 100))))
                    <div class="status-pill {{ $meta['class'] }}">
                        <span>{{ $meta['label'] }}</span>
                        <strong>{{ $count }}</strong>
                        <i style="width: {{ $percent }}%;"></i>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="panel dashboard-widget source-widget">
            <div class="widget-heading">
                <div>
                    <h2>Booking sources</h2>
                    <p>Where reservations are coming from this week.</p>
                </div>
                <span class="badge">7 days</span>
            </div>
            <div class="source-list">
                @forelse ($sourceMix as $source)
                    <div class="source-row">
                        <span>{{ $source['source'] }}</span>
                        <strong>{{ $source['count'] }}</strong>
                        <div class="mini-progress"><span style="width: {{ $source['percent'] }}%;"></span></div>
                    </div>
                @empty
                    <div class="empty-state">
                        <strong>No source data yet.</strong>
                        <p style="margin: 0;">Bookings from web, widget, and staff channels will appear here.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="grid dashboard-grid dashboard-bottom-grid">
        <div class="panel dashboard-widget">
            <div class="widget-heading">
                <div>
                    <h2>Today’s flow</h2>
                    <p>Bookings ordered by arrival time with table allocation and status.</p>
                </div>
                <a class="button subtle" href="{{ route('admin.diary', ['date' => $today->toDateString()]) }}">Full diary</a>
            </div>
            <div class="diary">
                @forelse ($todayBookings as $booking)
                    <article class="booking-card dashboard-booking-card">
                        <div>
                            <strong>{{ $booking->starts_at->format('H:i') }}</strong>
                            <p style="margin: 4px 0 0;">{{ $booking->service->name }}</p>
                        </div>
                        <div>
                            <h3>{{ $booking->customer->full_name }} · {{ $booking->party_size }} guests</h3>
                            <p style="margin: 0;">{{ $booking->booking_reference }} · {{ ucfirst($booking->source) }}</p>
                            <div class="table-list">
                                @foreach ($booking->tables as $table)
                                    <span class="badge">{{ $table->name }}</span>
                                @endforeach
                            </div>
                        </div>
                        <span class="badge status-badge {{ $statusPalette[$booking->status]['class'] ?? 'slate' }}">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
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
            <div class="panel dashboard-widget quick-widget">
                <h2>Quick actions</h2>
                <div class="quick-actions dashboard-actions">
                    <a class="button" href="{{ route('admin.bookings.create') }}">New booking</a>
                    <a class="button" href="{{ route('admin.availability.index') }}">Edit hours</a>
                    <a class="button" href="{{ route('admin.services.index') }}">Services</a>
                    <a class="button" href="{{ route('admin.areas.index') }}">Tables</a>
                </div>
            </div>

            <div class="panel dashboard-widget">
                <div class="widget-heading">
                    <div>
                        <h2>Upcoming</h2>
                        <p>The next five live reservations.</p>
                    </div>
                </div>
                <div class="staff-list upcoming-list">
                    @forelse ($upcomingBookings as $booking)
                        <article>
                            <span>{{ $booking->starts_at->format('D j M') }}</span>
                            <h3>{{ $booking->starts_at->format('H:i') }} · {{ $booking->customer->full_name }}</h3>
                            <p style="margin: 0;">{{ $booking->party_size }} guests · {{ $booking->service->name }}</p>
                        </article>
                    @empty
                        <div class="empty-state">
                            <strong>No upcoming bookings.</strong>
                            <p style="margin: 0;">Online and staff-created bookings will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="panel dashboard-widget setup-widget">
                <h2>Setup health</h2>
                <div class="setup-grid">
                    <span><strong>{{ $venue->services->count() }}</strong> services</span>
                    <span><strong>{{ $venue->diningAreas->count() }}</strong> areas</span>
                    <span><strong>{{ $venue->tables->count() }}</strong> tables</span>
                    <span><strong>{{ $venue->contact_email ? 'On' : 'Off' }}</strong> email</span>
                </div>
                <p style="margin-bottom: 0;">Booking window: {{ $venue->maximum_advance_booking_days }} days · Lead time: {{ $venue->minimum_lead_time_minutes }} mins</p>
            </div>
        </aside>
    </div>
</section>
@endsection
