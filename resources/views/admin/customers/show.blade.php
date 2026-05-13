@extends('layouts.app', ['title' => $customer->full_name, 'venue' => $venue])

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
@endphp

<section class="hero compact customer-profile-hero">
    <div class="shell">
        <div class="dashboard-hero-grid">
            <div>
                <div class="eyebrow">Customer profile</div>
                <h1>{{ $customer->full_name }}</h1>
                <p>{{ $customer->email }} · {{ $customer->phone }}</p>
                <div class="crm-badge-row hero-badges">
                    @if ($customer->is_vip)
                        <span class="badge status-badge amber">VIP guest</span>
                    @endif
                    @if ($customer->marketing_opt_in)
                        <span class="badge status-badge green">Marketing opt-in</span>
                    @endif
                    @if ($customer->has_profile_notes)
                        <span class="badge status-badge cyan">Staff notes</span>
                    @endif
                </div>
            </div>
            <div class="insight-card gradient-amber">
                <div>
                    <span>Guest value</span>
                    <strong>{{ $metrics['total_bookings'] }}</strong>
                    <p>{{ $metrics['total_covers'] }} covers · {{ $metrics['average_party_size'] }} average party</p>
                </div>
                <div class="orbital-chart" style="--value: {{ min(100, $metrics['total_covers'] * 6) }};">
                    <span>{{ $metrics['total_covers'] }}</span>
                </div>
            </div>
        </div>
        <div class="actions">
            <a class="button primary" href="{{ route('admin.customers.edit', $customer) }}">Edit profile</a>
            <a class="button subtle" href="{{ route('admin.customers.index') }}">Back to CRM</a>
        </div>
    </div>
</section>

<section class="shell customer-crm-suite">
    @if (session('status'))
        <div class="panel success"><p style="margin: 0;">{{ session('status') }}</p></div>
    @endif

    <div class="crm-summary-grid">
        <article class="booking-summary-card violet"><span>Total bookings</span><strong>{{ $metrics['total_bookings'] }}</strong><small>All linked visits</small></article>
        <article class="booking-summary-card green"><span>Total covers</span><strong>{{ $metrics['total_covers'] }}</strong><small>Excludes cancelled/no-show</small></article>
        <article class="booking-summary-card cyan"><span>Average party</span><strong>{{ $metrics['average_party_size'] }}</strong><small>Active bookings</small></article>
        <article class="booking-summary-card red"><span>Cancellations/no-shows</span><strong>{{ $metrics['cancelled_no_show_count'] }}</strong><small>Guest-care watchlist</small></article>
    </div>

    <div class="customer-profile-grid">
        <div class="panel dashboard-widget customer-intel-card">
            <div class="widget-heading">
                <div>
                    <h2>Guest intelligence</h2>
                    <p>Private staff-only CRM information. This does not appear on public booking pages.</p>
                </div>
            </div>
            <dl class="crm-detail-list">
                <div><dt>Allergies</dt><dd>{{ $customer->allergies ?: 'None recorded' }}</dd></div>
                <div><dt>Dietary requirements</dt><dd>{{ $customer->dietary_requirements ?: 'None recorded' }}</dd></div>
                <div><dt>Preferences</dt><dd>{{ $customer->preferences ?: 'None recorded' }}</dd></div>
                <div><dt>Favourite area</dt><dd>{{ $customer->favouriteDiningArea?->name ?: 'None recorded' }}</dd></div>
                <div><dt>Favourite table</dt><dd>{{ $customer->favouriteRestaurantTable?->name ?: 'None recorded' }}</dd></div>
                <div class="full"><dt>Internal notes</dt><dd>{{ $customer->notes ?: 'No internal notes recorded.' }}</dd></div>
            </dl>
        </div>

        <div class="panel dashboard-widget">
            <div class="widget-heading">
                <div>
                    <h2>Visit dates</h2>
                    <p>Quick read on previous and upcoming relationship activity.</p>
                </div>
            </div>
            <dl class="crm-detail-list compact">
                <div><dt>Last booking</dt><dd>{{ $metrics['last_booking_date']?->format('d M Y H:i') ?: 'No past booking' }}</dd></div>
                <div><dt>Next booking</dt><dd>{{ $metrics['next_booking_date']?->format('d M Y H:i') ?: 'None scheduled' }}</dd></div>
                <div><dt>Created</dt><dd>{{ $customer->created_at->format('d M Y H:i') }}</dd></div>
                <div><dt>Updated</dt><dd>{{ $customer->updated_at->format('d M Y H:i') }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="panel dashboard-widget">
        <div class="widget-heading">
            <div>
                <h2>Upcoming bookings</h2>
                <p>Future visits linked to this profile.</p>
            </div>
        </div>
        @if ($upcomingBookings->isEmpty())
            <div class="empty-state compact">
                <strong>No upcoming bookings.</strong>
                <p style="margin: 0;">Create a staff booking when this guest calls or returns.</p>
            </div>
        @else
            <div class="booking-mini-list">
                @foreach ($upcomingBookings as $booking)
                    <a href="{{ route('admin.diary', ['date' => $booking->starts_at->toDateString(), 'search' => $booking->booking_reference]) }}">
                        <strong>{{ $booking->starts_at->format('D d M · H:i') }}</strong>
                        <span>{{ $booking->service->name }} · {{ $booking->party_size }} covers · {{ $booking->tables->pluck('name')->join(', ') ?: 'Unassigned' }}</span>
                        <em class="badge status-badge {{ $statusMeta[$booking->status]['class'] ?? 'slate' }}">{{ $statusMeta[$booking->status]['label'] ?? $booking->status }}</em>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <div class="panel dashboard-widget">
        <div class="widget-heading">
            <div>
                <h2>Booking history</h2>
                <p>Completed, cancelled, no-show and older guest activity.</p>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Service</th>
                        <th>Covers</th>
                        <th>Tables</th>
                        <th>Status</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pastBookings as $booking)
                        <tr>
                            <td>{{ $booking->starts_at->format('d M Y H:i') }}</td>
                            <td>{{ $booking->service->name }}</td>
                            <td>{{ $booking->party_size }}</td>
                            <td>{{ $booking->tables->pluck('name')->join(', ') ?: 'Unassigned' }}</td>
                            <td><span class="badge status-badge {{ $statusMeta[$booking->status]['class'] ?? 'slate' }}">{{ $statusMeta[$booking->status]['label'] ?? $booking->status }}</span></td>
                            <td>{{ $booking->booking_reference }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No past booking history yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
