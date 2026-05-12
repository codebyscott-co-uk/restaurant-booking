@php
    $currentUser = auth()->user();
    $currentVenue = $venue ?? $currentUser?->venue;

    $navSections = [
        [
            'label' => 'Dashboard',
            'icon' => 'dashboard',
            'active' => request()->routeIs('admin.dashboard'),
            'items' => [
                ['label' => 'Overview', 'href' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard')],
            ],
        ],
        [
            'label' => 'Bookings',
            'icon' => 'bookings',
            'active' => request()->routeIs('admin.diary') || request()->routeIs('admin.bookings.*'),
            'items' => [
                ['label' => 'Booking diary', 'href' => route('admin.diary'), 'active' => request()->routeIs('admin.diary')],
                ['label' => 'New booking', 'href' => route('admin.bookings.create'), 'active' => request()->routeIs('admin.bookings.create')],
            ],
        ],
        [
            'label' => 'Availability',
            'icon' => 'availability',
            'active' => request()->routeIs('admin.availability.*'),
            'items' => [
                ['label' => 'Opening hours', 'href' => route('admin.availability.index'), 'active' => request()->routeIs('admin.availability.*')],
            ],
        ],
        [
            'label' => 'Tables & Areas',
            'icon' => 'tables',
            'active' => request()->routeIs('admin.areas.*') || request()->routeIs('admin.tables.*'),
            'items' => [
                ['label' => 'Floor plan setup', 'href' => route('admin.areas.index'), 'active' => request()->routeIs('admin.areas.index')],
                ['label' => 'Add table', 'href' => route('admin.tables.create'), 'active' => request()->routeIs('admin.tables.create')],
                ['label' => 'Add area', 'href' => route('admin.areas.create'), 'active' => request()->routeIs('admin.areas.create')],
            ],
        ],
        [
            'label' => 'Services',
            'icon' => 'services',
            'active' => request()->routeIs('admin.services.*'),
            'items' => [
                ['label' => 'Service windows', 'href' => route('admin.services.index'), 'active' => request()->routeIs('admin.services.index')],
                ['label' => 'Add service', 'href' => route('admin.services.create'), 'active' => request()->routeIs('admin.services.create')],
            ],
        ],
        [
            'label' => 'Customers',
            'icon' => 'customers',
            'active' => false,
            'items' => [
                ['label' => 'Guest CRM', 'disabled' => true, 'meta' => 'Coming soon'],
            ],
        ],
        [
            'label' => 'Billing',
            'icon' => 'billing',
            'active' => false,
            'items' => [
                ['label' => 'Subscription', 'disabled' => true, 'meta' => 'Coming soon'],
            ],
        ],
        [
            'label' => 'Settings',
            'icon' => 'settings',
            'active' => request()->routeIs('admin.settings.*'),
            'items' => [
                ['label' => 'Business settings', 'href' => route('admin.settings.edit'), 'active' => request()->routeIs('admin.settings.*')],
            ],
        ],
        [
            'label' => 'Staff',
            'icon' => 'staff',
            'active' => request()->routeIs('admin.staff.*'),
            'items' => [
                ['label' => 'Staff users', 'href' => route('admin.staff.index'), 'active' => request()->routeIs('admin.staff.index')],
                ['label' => 'Add staff user', 'href' => route('admin.staff.create'), 'active' => request()->routeIs('admin.staff.create')],
            ],
        ],
    ];
@endphp

<div class="admin-shell" data-admin-shell>
    <aside class="admin-sidebar" data-admin-sidebar>
        <a class="admin-brand" href="{{ route('admin.dashboard') }}">
            @if ($currentVenue?->logo_url)
                <img src="{{ $currentVenue->logo_url }}" alt="{{ $currentVenue->name }} logo">
            @else
                <span>{{ substr($currentVenue->name ?? 'R', 0, 1) }}</span>
            @endif
            <strong>{{ $currentVenue->name ?? 'Restaurant Booking' }}</strong>
        </a>

        <nav class="admin-nav" aria-label="Admin navigation">
            @foreach ($navSections as $section)
                <details class="admin-nav-section" {{ $section['active'] ? 'open' : '' }}>
                    <summary>
                        <span class="admin-nav-title">
                            <span class="admin-nav-icon" aria-hidden="true">
                                @switch($section['icon'])
                                    @case('dashboard')
                                        <svg viewBox="0 0 24 24"><path d="M4 13h7V4H4v9Zm9 7h7V4h-7v16ZM4 20h7v-5H4v5Z"/></svg>
                                        @break
                                    @case('bookings')
                                        <svg viewBox="0 0 24 24"><path d="M7 2v3M17 2v3M4 9h16M6 5h12a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/></svg>
                                        @break
                                    @case('availability')
                                        <svg viewBox="0 0 24 24"><path d="M12 7v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        @break
                                    @case('tables')
                                        <svg viewBox="0 0 24 24"><path d="M4 7h16v5H4V7Zm2 5v7M18 12v7M8 17h8"/></svg>
                                        @break
                                    @case('services')
                                        <svg viewBox="0 0 24 24"><path d="M6 3v18M10 3v18M17 3c2 2 2 7 0 9v9"/></svg>
                                        @break
                                    @case('customers')
                                        <svg viewBox="0 0 24 24"><path d="M16 11a4 4 0 1 0-8 0M5 21a7 7 0 0 1 14 0"/></svg>
                                        @break
                                    @case('billing')
                                        <svg viewBox="0 0 24 24"><path d="M4 7h16v10H4V7Zm0 3h16M7 15h4"/></svg>
                                        @break
                                    @case('settings')
                                        <svg viewBox="0 0 24 24"><path d="M12 8a4 4 0 1 1 0 8 4 4 0 0 1 0-8Zm0-5v3M12 18v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M3 12h3M18 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"/></svg>
                                        @break
                                    @default
                                        <svg viewBox="0 0 24 24"><path d="M7 7h10v10H7V7Z"/></svg>
                                @endswitch
                            </span>
                            {{ $section['label'] }}
                        </span>
                        <svg class="admin-nav-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m8 10 4 4 4-4"/></svg>
                    </summary>
                    <div class="admin-nav-items">
                        @foreach ($section['items'] as $item)
                            @if ($item['disabled'] ?? false)
                                <span class="admin-nav-disabled">
                                    {{ $item['label'] }}
                                    <small>{{ $item['meta'] ?? '' }}</small>
                                </span>
                            @else
                                <a class="{{ ($item['active'] ?? false) ? 'active' : '' }}" href="{{ $item['href'] }}">{{ $item['label'] }}</a>
                            @endif
                        @endforeach
                    </div>
                </details>
            @endforeach
        </nav>
    </aside>

    <div class="admin-backdrop" data-admin-backdrop></div>

    <div class="admin-main">
        <header class="admin-topbar">
            <div class="admin-topbar-left">
                <label class="admin-search" aria-label="Search">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg>
                    <input type="search" placeholder="Search..." disabled>
                </label>
            </div>

            <div class="admin-topbar-actions">
                <a class="button primary" href="{{ route('admin.bookings.create') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    New booking
                </a>

                <button class="admin-icon-button" type="button" data-theme-toggle aria-label="Toggle light and dark mode">
                    <svg class="theme-icon theme-icon-moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12.8A8.5 8.5 0 1 1 11.2 3 6.5 6.5 0 0 0 21 12.8Z"/></svg>
                    <svg class="theme-icon theme-icon-sun" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4V2M12 22v-2M4.93 4.93 3.51 3.51M20.49 20.49l-1.42-1.42M4 12H2M22 12h-2M4.93 19.07l-1.42 1.42M20.49 3.51l-1.42 1.42M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/></svg>
                </button>

                <details class="admin-dropdown">
                    <summary class="admin-icon-button" aria-label="Notifications">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 9a6 6 0 1 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
                    </summary>
                    <div class="admin-dropdown-panel">
                        <strong>Notifications</strong>
                        <p>No new notifications yet.</p>
                    </div>
                </details>

                <details class="admin-dropdown">
                    <summary class="admin-profile-summary">
                        @if ($currentUser->avatar_url)
                            <img src="{{ $currentUser->avatar_url }}" alt="{{ $currentUser->name }}">
                        @else
                            <span>{{ strtoupper(substr($currentUser->name ?? 'S', 0, 1)) }}</span>
                        @endif
                        <strong>{{ $currentUser->name }}</strong>
                    </summary>
                    <div class="admin-dropdown-panel profile">
                        <div class="admin-profile-card">
                            @if ($currentUser->avatar_url)
                                <img src="{{ $currentUser->avatar_url }}" alt="{{ $currentUser->name }}">
                            @else
                                <span>{{ strtoupper(substr($currentUser->name ?? 'S', 0, 1)) }}</span>
                            @endif
                            <div>
                                <strong>{{ $currentUser->name }}</strong>
                                <p>{{ ucfirst($currentUser->role) }}</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.profile.edit') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM5 21a7 7 0 0 1 14 0"/></svg> My Profile</a>
                        <a href="{{ route('admin.settings.edit') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8a4 4 0 1 1 0 8 4 4 0 0 1 0-8Zm0-5v3M12 18v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M3 12h3M18 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"/></svg> Settings</a>
                        <a href="{{ route('admin.settings.edit') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16v10H4V7Zm0 3h16M7 15h4"/></svg> Billing</a>
                        <a href="mailto:hello@codebyscott.co.uk"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 9a3 3 0 1 1 5.2 2c-.9.8-2.2 1.4-2.2 3M12 18h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg> Help</a>
                        <form class="logout-form" method="post" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-logout" type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17 15 12l-5-5M15 12H3M21 3v18"/></svg> Logout</button>
                        </form>
                    </div>
                </details>
            </div>
        </header>

        <main class="admin-content">
            @yield('content')
        </main>

        <footer class="site-footer admin-footer">
            <div class="shell site-footer-inner">
                <a class="powered-by" href="https://codebyscott.co.uk" target="_blank" rel="noopener noreferrer">
                    <span>Powered by</span>
                    <img src="{{ asset('images/code-by-scott-logo.png') }}" alt="Code by Scott">
                </a>
                <small>Restaurant booking software by Code by Scott.</small>
            </div>
        </footer>
    </div>
</div>
