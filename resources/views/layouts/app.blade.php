<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Restaurant Booking' }}</title>
    <style>
        :root {
            --ink: #111827;
            --muted: #62706d;
            --soft: #8a9692;
            --line: #dfe7e4;
            --paper: #f7f8f4;
            --panel: #ffffff;
            --panel-soft: rgba(255, 255, 255, .78);
            --primary: {{ $venue->primary_colour ?? '#0f766e' }};
            --accent: {{ $venue->accent_colour ?? '#f59e0b' }};
            --blue: #2563eb;
            --danger: #dc2626;
            --success: #15803d;
            --shadow-sm: 0 8px 24px rgba(17, 24, 39, .08);
            --shadow-md: 0 18px 55px rgba(17, 24, 39, .12);
            --shadow-lg: 0 28px 90px rgba(17, 24, 39, .18);
            --radius: 8px;
            --focus: 0 0 0 4px color-mix(in srgb, var(--primary) 20%, transparent);
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--ink);
            background:
                linear-gradient(135deg, color-mix(in srgb, var(--primary) 8%, transparent), transparent 34%),
                linear-gradient(225deg, color-mix(in srgb, var(--accent) 10%, transparent), transparent 38%),
                linear-gradient(180deg, #ffffff 0%, var(--paper) 42%, #f3f6f4 100%);
            min-height: 100vh;
            text-rendering: optimizeLegibility;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image: linear-gradient(rgba(17, 24, 39, .026) 1px, transparent 1px), linear-gradient(90deg, rgba(17, 24, 39, .02) 1px, transparent 1px);
            background-size: 32px 32px;
            mask-image: linear-gradient(180deg, rgba(0,0,0,.9), transparent 72%);
        }
        a { color: inherit; transition: color .18s ease, background .18s ease, border-color .18s ease, transform .18s ease, box-shadow .18s ease; }
        .shell { width: min(1120px, calc(100% - 32px)); margin: 0 auto; }
        .topbar { position: sticky; top: 0; z-index: 10; background: rgba(255, 255, 255, .78); border-bottom: 1px solid rgba(223, 231, 228, .82); backdrop-filter: blur(18px) saturate(1.25); box-shadow: 0 10px 34px rgba(17, 24, 39, .05); }
        .topbar-inner { min-height: 68px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .brand { display: flex; align-items: center; gap: 12px; text-decoration: none; min-width: 0; }
        .brand:hover { transform: translateY(-1px); }
        .brand-logo { width: 42px; height: 42px; object-fit: contain; border-radius: var(--radius); border: 1px solid color-mix(in srgb, var(--primary) 18%, var(--line)); background: #fff; box-shadow: var(--shadow-sm); }
        .brand-text { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
        .brand strong { font-size: 18px; letter-spacing: 0; }
        .brand span { color: var(--muted); font-size: 13px; }
        .nav { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; justify-content: flex-end; }
        .nav a, .button, button { min-height: 44px; border: 1px solid color-mix(in srgb, var(--line) 86%, white); border-radius: var(--radius); background: rgba(255,255,255,.86); color: var(--ink); padding: 11px 14px; text-decoration: none; font-weight: 800; cursor: pointer; font: inherit; box-shadow: 0 1px 0 rgba(255,255,255,.9) inset, 0 8px 22px rgba(17, 24, 39, .045); }
        .nav a:hover, .button:hover, button:hover { border-color: color-mix(in srgb, var(--primary) 28%, var(--line)); transform: translateY(-1px); box-shadow: var(--shadow-sm); }
        .nav a:focus-visible, .button:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible { outline: 0; box-shadow: var(--focus); border-color: var(--primary); }
        .nav a.active { border-color: color-mix(in srgb, var(--primary) 38%, white); background: linear-gradient(180deg, color-mix(in srgb, var(--primary) 12%, white), color-mix(in srgb, var(--primary) 6%, white)); color: var(--primary); box-shadow: 0 10px 28px color-mix(in srgb, var(--primary) 12%, transparent); }
        .button.primary, button.primary { border-color: transparent; background: linear-gradient(135deg, var(--primary), color-mix(in srgb, var(--primary) 72%, var(--blue))); color: #fff; box-shadow: 0 14px 34px color-mix(in srgb, var(--primary) 28%, transparent); }
        .button.primary:hover, button.primary:hover { box-shadow: 0 18px 42px color-mix(in srgb, var(--primary) 34%, transparent); }
        .button.subtle, button.subtle { background: linear-gradient(180deg, color-mix(in srgb, var(--primary) 8%, white), white); border-color: color-mix(in srgb, var(--primary) 24%, white); color: var(--primary); }
        .button.danger, button.danger { border-color: color-mix(in srgb, var(--danger) 35%, white); color: var(--danger); background: #fff; }
        .button.danger:hover, button.danger:hover { background: #fff7f7; box-shadow: 0 14px 34px rgba(220, 38, 38, .11); }
        .logout-form { margin: 0; }
        main { animation: pageIn .42s ease both; }
        .hero { padding: 44px 0 28px; position: relative; }
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
        .panel { background: var(--panel-soft); border: 1px solid rgba(223, 231, 228, .9); border-radius: var(--radius); padding: 20px; box-shadow: var(--shadow-sm); backdrop-filter: blur(14px); animation: riseIn .45s ease both; }
        .panel:hover { box-shadow: var(--shadow-md); }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .field { display: grid; gap: 7px; }
        label { font-weight: 800; font-size: 13px; }
        input, select, textarea { width: 100%; min-height: 46px; border: 1px solid var(--line); border-radius: var(--radius); background: rgba(255,255,255,.92); color: var(--ink); padding: 11px 12px; font: inherit; transition: border-color .18s ease, box-shadow .18s ease, background .18s ease; }
        input:hover, select:hover, textarea:hover { border-color: color-mix(in srgb, var(--primary) 24%, var(--line)); background: #fff; }
        input[type="color"] { padding: 5px; }
        textarea { min-height: 100px; resize: vertical; }
        .full { grid-column: 1 / -1; }
        .slots { display: grid; grid-template-columns: repeat(auto-fit, minmax(96px, 1fr)); gap: 10px; margin-top: 12px; }
        .slot { position: relative; }
        .slot input { position: absolute; opacity: 0; pointer-events: none; }
        .slot span { display: flex; min-height: 46px; align-items: center; justify-content: center; border: 1px solid var(--line); border-radius: var(--radius); background: #fff; font-weight: 800; transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease, background .18s ease; }
        .slot:hover span { transform: translateY(-1px); border-color: color-mix(in srgb, var(--primary) 30%, var(--line)); box-shadow: var(--shadow-sm); }
        .slot input:checked + span { border-color: var(--primary); background: linear-gradient(180deg, color-mix(in srgb, var(--primary) 14%, white), white); color: var(--primary); box-shadow: 0 12px 28px color-mix(in srgb, var(--primary) 14%, transparent); }
        .errors { border-color: #fecaca; background: linear-gradient(180deg, #fff7f7, #fff); color: #991b1b; }
        .errors p, .error { color: #991b1b; }
        .success { border-color: #bbf7d0; background: linear-gradient(180deg, #f0fdf4, #fff); color: #166534; }
        .success p { color: #166534; }
        .empty-state { display: grid; gap: 10px; place-items: start; border: 1px dashed color-mix(in srgb, var(--primary) 24%, var(--line)); border-radius: var(--radius); padding: 22px; background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 5%, white), color-mix(in srgb, var(--accent) 6%, white)); }
        .muted { color: var(--muted); }
        .metric-row { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin: 0 0 18px; }
        .metric { position: relative; overflow: hidden; background: rgba(255,255,255,.84); border: 1px solid rgba(223, 231, 228, .9); border-radius: var(--radius); padding: 16px; box-shadow: var(--shadow-sm); transition: transform .18s ease, box-shadow .18s ease; animation: riseIn .45s ease both; }
        .metric:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .metric strong { display: block; font-size: 30px; letter-spacing: 0; }
        .metric span { color: var(--muted); font-weight: 700; font-size: 13px; }
        .diary { display: grid; gap: 12px; }
        .booking-card { display: grid; grid-template-columns: 120px minmax(0, 1fr) auto; gap: 14px; align-items: start; background: rgba(255,255,255,.92); border: 1px solid var(--line); border-left: 6px solid var(--primary); border-radius: var(--radius); padding: 14px; box-shadow: 0 10px 26px rgba(17, 24, 39, .055); transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease; animation: riseIn .42s ease both; }
        .booking-card:hover, .staff-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); border-color: color-mix(in srgb, var(--primary) 24%, var(--line)); }
        .badge { display: inline-flex; align-items: center; border-radius: 999px; border: 1px solid color-mix(in srgb, var(--line) 82%, white); padding: 5px 9px; font-size: 12px; font-weight: 800; color: var(--muted); background: rgba(255,255,255,.86); box-shadow: 0 4px 12px rgba(17, 24, 39, .04); }
        .table-list { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
        .notice { border: 1px solid color-mix(in srgb, var(--accent) 42%, white); background: linear-gradient(135deg, color-mix(in srgb, var(--accent) 14%, white), #fff); padding: 14px; border-radius: var(--radius); box-shadow: var(--shadow-sm); }
        .staff-list { display: grid; gap: 10px; }
        .staff-card { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 14px; align-items: center; border: 1px solid var(--line); border-radius: var(--radius); background: rgba(255,255,255,.9); padding: 14px; box-shadow: 0 8px 22px rgba(17, 24, 39, .045); transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease; }
        .actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
        .quick-actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .quick-actions a { min-height: 64px; display: flex; align-items: center; justify-content: center; text-align: center; background: linear-gradient(180deg, #fff, color-mix(in srgb, var(--paper) 56%, white)); }
        .logo-preview { width: 160px; max-width: 100%; border: 1px solid var(--line); border-radius: var(--radius); background: #fff; padding: 12px; box-shadow: var(--shadow-sm); }
        .site-footer { border-top: 1px solid rgba(223, 231, 228, .84); background: rgba(255,255,255,.78); margin-top: 28px; backdrop-filter: blur(14px); }
        .site-footer-inner { min-height: 88px; display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 20px 0; }
        .powered-by { display: flex; align-items: center; gap: 12px; color: var(--muted); text-decoration: none; font-weight: 700; }
        .powered-by img { display: block; height: 34px; width: auto; max-width: 180px; object-fit: contain; }
        .site-footer small { color: var(--muted); }
        .modal-backdrop { position: fixed; inset: 0; z-index: 40; display: grid; place-items: center; padding: 20px; background: rgba(17, 24, 39, .46); backdrop-filter: blur(10px); opacity: 0; pointer-events: none; transition: opacity .18s ease; }
        .modal-backdrop.is-open { opacity: 1; pointer-events: auto; }
        .modal { width: min(460px, 100%); border: 1px solid rgba(255,255,255,.55); border-radius: var(--radius); background: rgba(255,255,255,.94); box-shadow: var(--shadow-lg); padding: 22px; transform: translateY(14px) scale(.98); transition: transform .18s ease; }
        .modal-backdrop.is-open .modal { transform: translateY(0) scale(1); }
        .modal h2 { margin-bottom: 8px; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px; flex-wrap: wrap; }
        @keyframes pageIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes riseIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .001ms !important; scroll-behavior: auto !important; transition-duration: .001ms !important; }
        }
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
            .actions, .modal-actions { align-items: stretch; flex-direction: column; }
            .actions > *, .modal-actions > * { width: 100%; justify-content: center; }
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
    <div class="modal-backdrop" data-confirm-modal hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="confirm-title" aria-describedby="confirm-message">
            <div class="eyebrow">Please confirm</div>
            <h2 id="confirm-title">Are you sure?</h2>
            <p id="confirm-message">This action cannot be undone.</p>
            <div class="modal-actions">
                <button class="subtle" type="button" data-modal-cancel>Cancel</button>
                <button class="danger" type="button" data-modal-confirm>Confirm</button>
            </div>
        </div>
    </div>
    <script>
        (() => {
            const modal = document.querySelector('[data-confirm-modal]');
            const message = document.querySelector('#confirm-message');
            const cancelButton = document.querySelector('[data-modal-cancel]');
            const confirmButton = document.querySelector('[data-modal-confirm]');
            let pendingForm = null;

            const closeModal = () => {
                modal.classList.remove('is-open');
                modal.setAttribute('hidden', '');
                pendingForm = null;
            };

            document.querySelectorAll('form[data-confirm]').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    if (form.dataset.confirmed === 'true') {
                        return;
                    }

                    event.preventDefault();
                    pendingForm = form;
                    message.textContent = form.dataset.confirm || 'This action cannot be undone.';
                    modal.removeAttribute('hidden');
                    requestAnimationFrame(() => modal.classList.add('is-open'));
                    confirmButton.focus();
                });
            });

            cancelButton.addEventListener('click', closeModal);
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal();
                }
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                    closeModal();
                }
            });
            confirmButton.addEventListener('click', () => {
                if (! pendingForm) {
                    return;
                }

                pendingForm.dataset.confirmed = 'true';
                pendingForm.requestSubmit();
            });
        })();
    </script>
</body>
</html>
