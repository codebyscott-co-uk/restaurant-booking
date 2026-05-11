<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Restaurant Booking' }}</title>
    <style>
        :root {
            --ink: #17211f;
            --muted: #5f6f6b;
            --line: #dfe7e4;
            --paper: #fbfbf7;
            --panel: #ffffff;
            --primary: {{ $venue->primary_colour ?? '#0f766e' }};
            --accent: {{ $venue->accent_colour ?? '#f59e0b' }};
            --danger: #dc2626;
            --success: #15803d;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color: var(--ink); background: var(--paper); }
        a { color: inherit; }
        .shell { width: min(1120px, calc(100% - 32px)); margin: 0 auto; }
        .topbar { position: sticky; top: 0; z-index: 10; background: rgba(251, 251, 247, .94); border-bottom: 1px solid var(--line); backdrop-filter: blur(12px); }
        .topbar-inner { min-height: 68px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .brand { display: flex; align-items: center; gap: 12px; text-decoration: none; min-width: 0; }
        .brand-logo { width: 42px; height: 42px; object-fit: contain; border-radius: 8px; border: 1px solid var(--line); background: #fff; }
        .brand-text { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
        .brand strong { font-size: 18px; letter-spacing: 0; }
        .brand span { color: var(--muted); font-size: 13px; }
        .nav { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; justify-content: flex-end; }
        .nav a, .button, button { min-height: 44px; border: 1px solid var(--line); border-radius: 8px; background: var(--panel); color: var(--ink); padding: 11px 14px; text-decoration: none; font-weight: 700; cursor: pointer; font: inherit; }
        .nav a.active { border-color: var(--primary); background: color-mix(in srgb, var(--primary) 10%, white); color: var(--primary); }
        .button.primary, button.primary { border-color: var(--primary); background: var(--primary); color: #fff; }
        .button.subtle, button.subtle { background: color-mix(in srgb, var(--primary) 7%, white); border-color: color-mix(in srgb, var(--primary) 24%, white); color: var(--primary); }
        .button.danger, button.danger { border-color: color-mix(in srgb, var(--danger) 35%, white); color: var(--danger); }
        .logout-form { margin: 0; }
        .hero { padding: 34px 0 22px; }
        .hero.compact { padding-bottom: 12px; }
        .eyebrow { color: var(--primary); font-weight: 800; text-transform: uppercase; font-size: 12px; letter-spacing: .08em; }
        h1 { margin: 10px 0; font-size: clamp(34px, 7vw, 68px); line-height: .96; letter-spacing: 0; max-width: 820px; }
        h2 { margin: 0 0 14px; font-size: 24px; letter-spacing: 0; }
        h3 { margin: 0 0 8px; font-size: 17px; letter-spacing: 0; }
        p { line-height: 1.55; color: var(--muted); }
        .grid { display: grid; gap: 18px; }
        .dashboard-grid { grid-template-columns: minmax(0, .9fr) minmax(320px, .45fr); align-items: start; }
        .booking-grid { grid-template-columns: minmax(0, 1.1fr) minmax(320px, .9fr); align-items: start; }
        .settings-grid { grid-template-columns: minmax(0, 1fr) minmax(280px, .45fr); align-items: start; }
        .panel { background: var(--panel); border: 1px solid var(--line); border-radius: 8px; padding: 20px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .field { display: grid; gap: 7px; }
        label { font-weight: 800; font-size: 13px; }
        input, select, textarea { width: 100%; min-height: 46px; border: 1px solid var(--line); border-radius: 8px; background: #fff; color: var(--ink); padding: 11px 12px; font: inherit; }
        input[type="color"] { padding: 5px; }
        textarea { min-height: 100px; resize: vertical; }
        .full { grid-column: 1 / -1; }
        .slots { display: grid; grid-template-columns: repeat(auto-fit, minmax(96px, 1fr)); gap: 10px; margin-top: 12px; }
        .slot { position: relative; }
        .slot input { position: absolute; opacity: 0; pointer-events: none; }
        .slot span { display: flex; min-height: 46px; align-items: center; justify-content: center; border: 1px solid var(--line); border-radius: 8px; background: #fff; font-weight: 800; }
        .slot input:checked + span { border-color: var(--primary); background: color-mix(in srgb, var(--primary) 12%, white); color: var(--primary); }
        .errors { border-color: #fecaca; background: #fff7f7; color: #991b1b; }
        .errors p, .error { color: #991b1b; }
        .success { border-color: #bbf7d0; background: #f0fdf4; color: #166534; }
        .success p { color: #166534; }
        .empty-state { display: grid; gap: 10px; place-items: start; border: 1px dashed var(--line); border-radius: 8px; padding: 22px; background: color-mix(in srgb, var(--paper) 70%, white); }
        .muted { color: var(--muted); }
        .metric-row { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin: 0 0 18px; }
        .metric { background: var(--panel); border: 1px solid var(--line); border-radius: 8px; padding: 14px; }
        .metric strong { display: block; font-size: 28px; }
        .metric span { color: var(--muted); font-weight: 700; font-size: 13px; }
        .diary { display: grid; gap: 12px; }
        .booking-card { display: grid; grid-template-columns: 120px minmax(0, 1fr) auto; gap: 14px; align-items: start; background: var(--panel); border: 1px solid var(--line); border-left: 6px solid var(--primary); border-radius: 8px; padding: 14px; }
        .badge { display: inline-flex; align-items: center; border-radius: 999px; border: 1px solid var(--line); padding: 5px 9px; font-size: 12px; font-weight: 800; color: var(--muted); background: #fff; }
        .table-list { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
        .notice { border: 1px solid color-mix(in srgb, var(--accent) 42%, white); background: color-mix(in srgb, var(--accent) 13%, white); padding: 14px; border-radius: 8px; }
        .staff-list { display: grid; gap: 10px; }
        .staff-card { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 14px; align-items: center; border: 1px solid var(--line); border-radius: 8px; background: #fff; padding: 14px; }
        .actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
        .quick-actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .quick-actions a { min-height: 64px; display: flex; align-items: center; justify-content: center; text-align: center; }
        .logo-preview { width: 160px; max-width: 100%; border: 1px solid var(--line); border-radius: 8px; background: #fff; padding: 12px; }
        .site-footer { border-top: 1px solid var(--line); background: #fff; margin-top: 28px; }
        .site-footer-inner { min-height: 88px; display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 20px 0; }
        .powered-by { display: flex; align-items: center; gap: 12px; color: var(--muted); text-decoration: none; font-weight: 700; }
        .powered-by img { display: block; height: 34px; width: auto; max-width: 180px; object-fit: contain; }
        .site-footer small { color: var(--muted); }
        @media (max-width: 780px) {
            .shell { width: min(100% - 24px, 1120px); }
            .topbar-inner { align-items: flex-start; flex-direction: column; padding: 12px 0; }
            .nav { width: 100%; justify-content: flex-start; overflow-x: auto; flex-wrap: nowrap; padding-bottom: 4px; }
            .nav a, .nav form, .nav button { flex: 0 0 auto; text-align: center; white-space: nowrap; }
            .booking-grid, .dashboard-grid, .settings-grid, .form-grid, .metric-row, .staff-card, .quick-actions { grid-template-columns: 1fr; }
            .booking-card { grid-template-columns: 1fr; }
            .site-footer-inner { align-items: flex-start; flex-direction: column; }
            .powered-by img { height: 30px; max-width: 160px; }
            h1 { font-size: 42px; }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="shell topbar-inner">
            <a class="brand" href="{{ route('bookings.create') }}">
                @if (($venue ?? null)?->logo_url)
                    <img class="brand-logo" src="{{ $venue->logo_url }}" alt="{{ $venue->name }} logo">
                @endif
                <span class="brand-text">
                    <strong>{{ $venue->name ?? 'Restaurant Booking' }}</strong>
                    <span>Book a table online</span>
                </span>
            </a>
            <nav class="nav">
                <a href="{{ route('bookings.create') }}">Book</a>
                @auth
                    <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
                    <a class="{{ request()->routeIs('admin.diary') || request()->routeIs('admin.bookings.*') ? 'active' : '' }}" href="{{ route('admin.diary') }}">Diary</a>
                    <a class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}" href="{{ route('admin.services.index') }}">Services</a>
                    <a class="{{ request()->routeIs('admin.availability.*') ? 'active' : '' }}" href="{{ route('admin.availability.index') }}">Availability</a>
                    <a class="{{ request()->routeIs('admin.areas.*') || request()->routeIs('admin.tables.*') ? 'active' : '' }}" href="{{ route('admin.areas.index') }}">Tables</a>
                    <a class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}">Settings</a>
                    <a class="{{ request()->routeIs('admin.staff.*') ? 'active' : '' }}" href="{{ route('admin.staff.index') }}">Staff</a>
                    <form class="logout-form" method="post" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Staff login</a>
                @endauth
            </nav>
        </div>
    </header>
    <main>
        @yield('content')
    </main>
    <footer class="site-footer">
        <div class="shell site-footer-inner">
            <a class="powered-by" href="https://codebyscott.co.uk" target="_blank" rel="noopener noreferrer">
                <span>Powered by</span>
                <img src="{{ asset('images/code-by-scott-logo.png') }}" alt="Code by Scott">
            </a>
            <small>Restaurant booking software by Code by Scott.</small>
        </div>
    </footer>
</body>
</html>
