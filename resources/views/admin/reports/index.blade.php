@extends('layouts.app', ['title' => 'Analytics & Reports', 'venue' => $venue])

@section('content')
@php
    $metrics = $report['metrics'];
    $range = $report['range'];
    $statusPalette = [
        'confirmed' => 'violet',
        'seated' => 'cyan',
        'completed' => 'green',
        'pending' => 'amber',
        'cancelled' => 'red',
        'no_show' => 'slate',
    ];
    $advancedReports = [
        'bookings' => 'Export bookings',
        'covers' => 'Export covers',
        'services' => 'Export service performance',
        'customers' => 'Export customer activity',
        'operations' => 'Export operations',
    ];
@endphp

<section class="hero compact dashboard-hero">
    <div class="shell">
        <div class="dashboard-hero-grid">
            <div>
                <div class="eyebrow">Analytics & reports</div>
                <h1>{{ $venue->name }}</h1>
                <p>Booking demand, cover performance, guest behaviour and operational signals for {{ strtolower($range['label']) }}.</p>
            </div>

            <div class="insight-card gradient-cyan">
                <div>
                    <span>Covers booked</span>
                    <strong>{{ $metrics['covers'] }}</strong>
                    <p>{{ $metrics['cover_trend'] === null ? 'Comparison appears once a previous period has data.' : (($metrics['cover_trend'] >= 0 ? '+' : '').$metrics['cover_trend'].'% vs previous period') }}</p>
                </div>
                <div class="orbital-chart" style="--value: {{ min(100, max(0, $metrics['covers'])) }};">
                    <span>{{ $metrics['average_party_size'] }}</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="shell reports-suite">
    <form class="panel report-filters" method="get" action="{{ route('admin.reports.index') }}">
        <div>
            <label for="range">Date range</label>
            <select id="range" name="range">
                <option value="today" @selected($range['preset'] === 'today')>Today</option>
                <option value="last_7_days" @selected($range['preset'] === 'last_7_days')>Last 7 days</option>
                <option value="last_30_days" @selected($range['preset'] === 'last_30_days')>Last 30 days</option>
                <option value="this_month" @selected($range['preset'] === 'this_month')>This month</option>
                <option value="last_month" @selected($range['preset'] === 'last_month')>Last month</option>
                <option value="custom" @selected($range['preset'] === 'custom')>Custom</option>
            </select>
        </div>
        <div>
            <label for="start_date">Start</label>
            <input id="start_date" name="start_date" type="date" value="{{ request('start_date', $range['start']->toDateString()) }}">
        </div>
        <div>
            <label for="end_date">End</label>
            <input id="end_date" name="end_date" type="date" value="{{ request('end_date', $range['end']->toDateString()) }}">
        </div>
        <button class="primary" type="submit">Apply filters</button>
    </form>

    <div class="dashboard-kpis reports-kpis">
        <article class="kpi-card violet">
            <span>Total bookings</span>
            <strong>{{ $metrics['total_bookings'] }}</strong>
            <small>{{ $metrics['confirmed_bookings'] }} confirmed in this period</small>
            <div class="sparkline" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
        </article>
        <article class="kpi-card green">
            <span>Covers booked</span>
            <strong>{{ $metrics['covers'] }}</strong>
            <small>Average party size {{ $metrics['average_party_size'] }}</small>
            <div class="sparkline" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
        </article>
        <article class="kpi-card red">
            <span>Cancelled</span>
            <strong>{{ $metrics['cancelled_bookings'] }}</strong>
            <small>{{ $metrics['cancellation_rate'] }}% cancellation rate</small>
            <div class="mini-progress"><span style="width: {{ min(100, $metrics['cancellation_rate']) }}%;"></span></div>
        </article>
        <article class="kpi-card amber">
            <span>Upcoming</span>
            <strong>{{ $metrics['upcoming_bookings'] }}</strong>
            <small>{{ $metrics['no_show_bookings'] }} no-shows in range</small>
            <div class="sparkline" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
        </article>
    </div>

    <div class="dashboard-mosaic reports-mosaic">
        <section class="panel dashboard-widget">
            <div class="widget-heading">
                <div>
                    <h2>Bookings by day</h2>
                    <p>Daily bookings and cover demand for the selected range.</p>
                </div>
                <span class="badge">{{ $range['label'] }}</span>
            </div>
            <div class="flow-chart report-flow-chart">
                @forelse ($report['bookingsByDay'] as $day)
                    @php($height = max(10, min(100, (int) round(($day['covers'] / $report['maxDayCovers']) * 100))))
                    <div class="flow-column" style="--height: {{ $height }}%;">
                        <span>{{ $day['covers'] }}</span>
                        <i></i>
                        <small>{{ $day['label'] }}</small>
                    </div>
                @empty
                    <div class="empty-state">
                        <strong>No bookings in this range.</strong>
                        <p style="margin: 0;">Try widening the date range or create bookings in the diary.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="panel dashboard-widget">
            <div class="widget-heading">
                <div>
                    <h2>Service performance</h2>
                    <p>Bookings, covers and cancellation signals by service.</p>
                </div>
            </div>
            <div class="service-bars">
                @forelse ($report['servicePerformance'] as $service)
                    @php($width = max(7, min(100, (int) round(($service['covers'] / $report['maxServiceCovers']) * 100))))
                    <div class="service-bar">
                        <div>
                            <strong>{{ $service['name'] }}</strong>
                            <span>{{ $service['bookings'] }} bookings · {{ $service['covers'] }} covers · avg {{ $service['average_party'] }}</span>
                        </div>
                        <div class="bar-track"><span style="width: {{ $width }}%;"></span></div>
                    </div>
                @empty
                    <div class="empty-state">
                        <strong>No service data yet.</strong>
                        <p style="margin: 0;">Service performance appears once bookings are created.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="panel dashboard-widget">
            <div class="widget-heading">
                <div>
                    <h2>Bookings by status</h2>
                    <p>Operational state across the selected period.</p>
                </div>
            </div>
            <div class="status-grid">
                @foreach ($report['statusCounts'] as $status => $count)
                    @php($percent = max(4, min(100, (int) round(($count / max(1, $metrics['total_bookings'])) * 100))))
                    <div class="status-pill {{ $statusPalette[$status] ?? 'slate' }}">
                        <span>{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                        <strong>{{ $count }}</strong>
                        <i style="width: {{ $percent }}%;"></i>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="panel dashboard-widget">
            <div class="widget-heading">
                <div>
                    <h2>Busiest times</h2>
                    <p>Useful for staffing and floor planning.</p>
                </div>
            </div>
            <div class="source-list">
                @forelse ($report['busiestTimes'] as $slot)
                    <div class="source-row">
                        <span>{{ $slot['time'] }}</span>
                        <strong>{{ $slot['covers'] }} covers</strong>
                        <div class="mini-progress"><span style="width: {{ min(100, max(8, $slot['covers'] * 5)) }}%;"></span></div>
                    </div>
                @empty
                    <div class="empty-state">
                        <strong>No time patterns yet.</strong>
                        <p style="margin: 0;">Arrival-time intelligence builds as bookings are added.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="grid dashboard-grid reports-grid">
        <section class="panel dashboard-widget">
            <div class="widget-heading">
                <div>
                    <h2>Bookings report</h2>
                    <p>Guest reservations in the selected range.</p>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Guest</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Covers</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report['bookings']->take(12) as $booking)
                            <tr>
                                <td>{{ $booking->booking_reference }}</td>
                                <td>{{ $booking->customer?->full_name }}</td>
                                <td>{{ $booking->service?->name }}</td>
                                <td>{{ $booking->starts_at->format('d M H:i') }}</td>
                                <td>{{ $booking->party_size }}</td>
                                <td><span class="badge status-badge {{ $statusPalette[$booking->status] ?? 'slate' }}">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6">No bookings found for this range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="grid">
            <section class="panel dashboard-widget">
                <div class="widget-heading">
                    <div>
                        <h2>Repeat guests</h2>
                        <p>Customers with more than one booking.</p>
                    </div>
                </div>
                <div class="staff-list">
                    @forelse ($report['repeatCustomers']->take(5) as $customer)
                        <article>
                            <span>{{ $customer['bookings'] }} bookings</span>
                            <h3>{{ $customer['name'] }}</h3>
                            <p style="margin: 0;">{{ $customer['email'] }}</p>
                        </article>
                    @empty
                        <div class="empty-state">
                            <strong>No repeat customers yet.</strong>
                            <p style="margin: 0;">Repeat booking summaries will appear as customer history grows.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="panel dashboard-widget">
                <div class="widget-heading">
                    <div>
                        <h2>Cancellation report</h2>
                        <p>Cancelled and no-show bookings by volume.</p>
                    </div>
                </div>
                <div class="setup-grid">
                    <span><strong>{{ $metrics['cancelled_bookings'] }}</strong> cancelled</span>
                    <span><strong>{{ $metrics['no_show_bookings'] }}</strong> no-show</span>
                    <span><strong>{{ $metrics['cancellation_rate'] }}%</strong> cancel rate</span>
                    <span><strong>{{ $metrics['no_show_rate'] }}%</strong> no-show rate</span>
                </div>
            </section>
        </aside>
    </div>

    <section class="panel dashboard-widget advanced-reports">
        <div class="widget-heading">
            <div>
                <h2>Advanced reporting</h2>
                <p>Premium analytics for forecasting, utilisation, retention and downloadable exports.</p>
            </div>
            @unless ($canUseAdvancedReports)
                <a class="button primary" href="{{ route('admin.billing.index') }}">Upgrade to {{ $requiredPlan['name'] ?? 'Premium' }}</a>
            @endunless
        </div>

        <div class="advanced-report-grid">
            <article class="advanced-card {{ $canUseAdvancedReports ? 'green' : 'locked' }}">
                <span>Forecasted covers</span>
                <strong>{{ $metrics['forecast_covers'] ?? 'Not enough data' }}</strong>
                <p>Projected next 7 days based on the selected period.</p>
            </article>
            <article class="advanced-card {{ $canUseAdvancedReports ? 'cyan' : 'locked' }}">
                <span>Table utilisation</span>
                <strong>{{ $canUseAdvancedReports ? $metrics['table_utilisation'].'%' : 'Premium' }}</strong>
                <p>Assigned covers measured against table capacity.</p>
            </article>
            <article class="advanced-card {{ $canUseAdvancedReports ? 'violet' : 'locked' }}">
                <span>Retention</span>
                <strong>{{ $canUseAdvancedReports ? $metrics['repeat_visit_rate'].'%' : 'Premium' }}</strong>
                <p>Repeat customers across the selected period.</p>
            </article>
            <article class="advanced-card {{ $canUseAdvancedReports ? 'amber' : 'locked' }}">
                <span>Previous period</span>
                <strong>{{ $canUseAdvancedReports ? $report['previous']['covers'].' covers' : 'Premium' }}</strong>
                <p>Comparison baseline for this date range.</p>
            </article>
        </div>

        <div class="export-actions">
            @foreach ($advancedReports as $key => $label)
                @if ($canUseAdvancedReports)
                    <a class="button subtle" href="{{ route('admin.reports.export', array_merge(['report' => $key], request()->query())) }}">{{ $label }}</a>
                @else
                    <a class="button" href="{{ route('admin.features.locked', 'advanced_reporting') }}">Locked: {{ $label }}</a>
                @endif
            @endforeach
        </div>
    </section>
</section>
@endsection
