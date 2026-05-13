@extends('layouts.app', ['title' => 'Customer CRM', 'venue' => $venue])

@section('content')
@php
    $sortUrl = fn (string $key) => route('admin.customers.index', array_filter([
        'search' => $search,
        'filter' => $filter,
        'sort' => $key,
        'direction' => $sort === $key && $direction === 'desc' ? 'asc' : 'desc',
    ]));
@endphp

<section class="hero compact customer-crm-hero">
    <div class="shell">
        <div class="dashboard-hero-grid">
            <div>
                <div class="eyebrow">Customer CRM</div>
                <h1>Customers</h1>
                <p>Guest profiles, visit history, preferences, allergies and private staff notes for {{ $venue->name }}.</p>
            </div>
            <div class="insight-card gradient-cyan">
                <div>
                    <span>Known guests</span>
                    <strong>{{ $summary['total'] }}</strong>
                    <p>{{ $summary['repeat'] }} repeat · {{ $summary['vip'] }} VIP · {{ $summary['with_notes'] }} with notes</p>
                </div>
                <div class="mini-bars" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
            </div>
        </div>
        <div class="actions">
            <a class="button primary" href="{{ route('admin.customers.create') }}">New customer</a>
            <a class="button subtle" href="{{ route('admin.diary') }}">Booking diary</a>
        </div>
    </div>
</section>

<section class="shell customer-crm-suite">
    @if (session('status'))
        <div class="panel success"><p style="margin: 0;">{{ session('status') }}</p></div>
    @endif

    <div class="crm-summary-grid">
        <article class="booking-summary-card violet"><span>Total customers</span><strong>{{ $summary['total'] }}</strong><small>Tenant-scoped profiles</small></article>
        <article class="booking-summary-card cyan"><span>Repeat guests</span><strong>{{ $summary['repeat'] }}</strong><small>More than one booking</small></article>
        <article class="booking-summary-card amber"><span>VIP guests</span><strong>{{ $summary['vip'] }}</strong><small>Flagged by staff</small></article>
        <article class="booking-summary-card green"><span>With notes</span><strong>{{ $summary['with_notes'] }}</strong><small>Preferences or allergy data</small></article>
    </div>

    <form method="get" action="{{ route('admin.customers.index') }}" class="panel booking-filter-panel">
        <div class="field">
            <label for="search">Search customers</label>
            <input id="search" name="search" value="{{ $search }}" placeholder="Name, email or phone">
        </div>
        <div class="field">
            <label for="filter">Filter</label>
            <select id="filter" name="filter">
                <option value="">All customers</option>
                <option value="vip" @selected($filter === 'vip')>VIP guests</option>
                <option value="notes" @selected($filter === 'notes')>Allergies/preferences/notes</option>
                <option value="repeat" @selected($filter === 'repeat')>Repeat guests</option>
            </select>
        </div>
        <div class="field">
            <label for="sort">Sort</label>
            <select id="sort" name="sort">
                <option value="last_visit" @selected($sort === 'last_visit')>Last visit</option>
                <option value="next_visit" @selected($sort === 'next_visit')>Next visit</option>
                <option value="bookings" @selected($sort === 'bookings')>Total bookings</option>
                <option value="name" @selected($sort === 'name')>Name</option>
            </select>
        </div>
        <div class="actions">
            <button class="primary" type="submit">Apply</button>
            <a class="button subtle" href="{{ route('admin.customers.index') }}">Reset</a>
        </div>
    </form>

    @if ($customers->isEmpty())
        <div class="empty-state">
            <strong>{{ $search || $filter ? 'No customers match these filters.' : 'No customers yet.' }}</strong>
            <p style="margin: 0;">Profiles are created automatically from bookings, or staff can add a guest manually.</p>
            <div class="actions">
                <a class="button primary" href="{{ route('admin.customers.create') }}">Create customer</a>
                <a class="button subtle" href="{{ route('admin.bookings.create') }}">Add booking</a>
            </div>
        </div>
    @else
        <div class="panel dashboard-widget">
            <div class="widget-heading">
                <div>
                    <h2>Customer directory</h2>
                    <p>Searchable operational CRM for regulars, VIPs and guest-care notes.</p>
                </div>
            </div>
            <div class="table-wrap customer-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th><a href="{{ $sortUrl('name') }}">Customer</a></th>
                            <th>Contact</th>
                            <th><a href="{{ $sortUrl('bookings') }}">Bookings</a></th>
                            <th><a href="{{ $sortUrl('last_visit') }}">Last visit</a></th>
                            <th><a href="{{ $sortUrl('next_visit') }}">Next booking</a></th>
                            <th>Signals</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($customers as $customer)
                            <tr>
                                <td>
                                    <strong>{{ $customer->full_name }}</strong>
                                    @if ($customer->favouriteDiningArea || $customer->favouriteRestaurantTable)
                                        <br><small>{{ $customer->favouriteRestaurantTable?->name ?: $customer->favouriteDiningArea?->name }}</small>
                                    @endif
                                </td>
                                <td>{{ $customer->phone }}<br><small>{{ $customer->email }}</small></td>
                                <td>{{ $customer->bookings_count }}</td>
                                <td>{{ $customer->last_visit_at ? \Illuminate\Support\Carbon::parse($customer->last_visit_at)->format('d M Y') : 'No visits yet' }}</td>
                                <td>{{ $customer->next_booking_at ? \Illuminate\Support\Carbon::parse($customer->next_booking_at)->format('d M Y H:i') : 'None booked' }}</td>
                                <td>
                                    <div class="crm-badge-row">
                                        @if ($customer->is_vip)
                                            <span class="badge status-badge amber">VIP</span>
                                        @endif
                                        @if ($customer->has_profile_notes)
                                            <span class="badge status-badge cyan">Notes</span>
                                        @endif
                                        @if ($customer->bookings_count > 1)
                                            <span class="badge status-badge violet">Repeat</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="actions compact">
                                        <a class="button subtle" href="{{ route('admin.customers.show', $customer) }}">Profile</a>
                                        <a class="button" href="{{ route('admin.customers.edit', $customer) }}">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $customers->links() }}</div>
        </div>
    @endif
</section>
@endsection
