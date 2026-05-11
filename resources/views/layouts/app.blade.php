<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Restaurant Booking' }}</title>
    <script>
        (() => {
            const savedTheme = localStorage.getItem('restaurant-admin-theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (savedTheme === 'dark' || (! savedTheme && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
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
            display: flex;
            flex-direction: column;
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
        .admin-menu-button { display: none; }
        .logout-form { margin: 0; }
        .admin-layout { display: grid; grid-template-columns: 280px minmax(0, 1fr); gap: 0; min-height: calc(100vh - 68px); flex: 1; }
        .admin-sidebar { position: sticky; top: 68px; align-self: start; height: calc(100vh - 68px); padding: 18px; border-right: 1px solid rgba(223, 231, 228, .9); background: rgba(255,255,255,.72); backdrop-filter: blur(18px) saturate(1.16); box-shadow: 18px 0 48px rgba(17, 24, 39, .05); overflow-y: auto; }
        .admin-sidebar-head { padding: 16px; border: 1px solid var(--line); border-radius: var(--radius); background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 9%, white), rgba(255,255,255,.92)); box-shadow: var(--shadow-sm); }
        .admin-sidebar-head strong { display: block; font-size: 16px; }
        .admin-sidebar-head span { color: var(--muted); font-size: 13px; }
        .admin-nav { display: grid; gap: 18px; margin-top: 18px; }
        .admin-nav-group { display: grid; gap: 7px; }
        .admin-nav-label { color: var(--soft); font-size: 11px; font-weight: 900; letter-spacing: .1em; text-transform: uppercase; padding: 0 10px; }
        .admin-nav a { display: flex; align-items: center; justify-content: space-between; gap: 12px; min-height: 46px; padding: 11px 12px; border: 1px solid transparent; border-radius: var(--radius); color: var(--muted); text-decoration: none; font-weight: 850; }
        .admin-nav a::after { content: ""; width: 7px; height: 7px; border-radius: 999px; background: transparent; }
        .admin-nav a:hover { color: var(--ink); background: rgba(255,255,255,.76); border-color: var(--line); transform: translateX(2px); box-shadow: var(--shadow-sm); }
        .admin-nav a.active { color: var(--primary); background: linear-gradient(180deg, color-mix(in srgb, var(--primary) 12%, white), rgba(255,255,255,.92)); border-color: color-mix(in srgb, var(--primary) 28%, white); box-shadow: 0 14px 34px color-mix(in srgb, var(--primary) 13%, transparent); }
        .admin-nav a.active::after { background: var(--primary); box-shadow: 0 0 0 4px color-mix(in srgb, var(--primary) 14%, transparent); }
        .admin-main { min-width: 0; display: flex; flex-direction: column; }
        .admin-backdrop { display: none; }
        main { animation: pageIn .42s ease both; flex: 1 0 auto; }
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
        .settings-form { display: grid; gap: 18px; }
        .settings-tabs { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 8px; padding: 8px; border: 1px solid var(--line); border-radius: var(--radius); background: rgba(255,255,255,.72); box-shadow: var(--shadow-sm); backdrop-filter: blur(14px); }
        .settings-tab { min-height: 46px; background: transparent; box-shadow: none; }
        .settings-tab.active { border-color: color-mix(in srgb, var(--primary) 34%, white); background: linear-gradient(180deg, color-mix(in srgb, var(--primary) 12%, white), rgba(255,255,255,.9)); color: var(--primary); box-shadow: 0 12px 28px color-mix(in srgb, var(--primary) 12%, transparent); }
        .settings-panel[hidden] { display: none; }
        .settings-panel-header { display: flex; align-items: start; justify-content: space-between; gap: 14px; margin-bottom: 18px; }
        .settings-panel-header p { margin: 0; }
        .settings-save { position: sticky; bottom: 16px; z-index: 5; display: flex; justify-content: flex-end; padding: 12px; border: 1px solid var(--line); border-radius: var(--radius); background: rgba(255,255,255,.82); box-shadow: var(--shadow-md); backdrop-filter: blur(14px); }
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
        .diary-toolbar { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 12px; align-items: end; margin-bottom: 18px; }
        .view-switch { display: flex; gap: 8px; flex-wrap: wrap; }
        .week-strip { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 10px; margin-bottom: 18px; }
        .day-tile { min-height: 96px; padding: 12px; border: 1px solid var(--line); border-radius: var(--radius); background: rgba(255,255,255,.82); text-decoration: none; box-shadow: var(--shadow-sm); }
        .day-tile.active { border-color: color-mix(in srgb, var(--primary) 36%, white); background: linear-gradient(180deg, color-mix(in srgb, var(--primary) 10%, white), #fff); }
        .day-tile strong { display: block; font-size: 20px; }
        .day-tile span { color: var(--muted); font-size: 12px; font-weight: 800; }
        .day-count { margin-top: 10px; color: var(--primary); font-weight: 900; }
        .timeline-section { display: grid; gap: 12px; }
        .timeline-day { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-top: 6px; }
        .booking-time { display: grid; gap: 4px; }
        .booking-time strong { font-size: 24px; }
        .booking-meta { display: flex; flex-wrap: wrap; gap: 6px; }
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
        .site-footer { border-top: 1px solid rgba(223, 231, 228, .84); background: rgba(255,255,255,.78); margin-top: auto; backdrop-filter: blur(14px); }
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
        .editor-toolbar { display: flex; flex-wrap: wrap; gap: 8px; padding: 8px; border: 1px solid var(--line); border-bottom: 0; border-radius: var(--radius) var(--radius) 0 0; background: rgba(255,255,255,.74); }
        .editor-toolbar button { min-height: 36px; padding: 7px 10px; }
        .wysiwyg-editor { min-height: 130px; border: 1px solid var(--line); border-radius: 0 0 var(--radius) var(--radius); background: rgba(255,255,255,.92); padding: 12px; line-height: 1.55; overflow: auto; }
        .wysiwyg-editor:focus { outline: 0; box-shadow: var(--focus); border-color: var(--primary); }
        .wysiwyg-editor p:first-child { margin-top: 0; }
        .wysiwyg-editor p:last-child { margin-bottom: 0; }
        @keyframes pageIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes riseIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .001ms !important; scroll-behavior: auto !important; transition-duration: .001ms !important; }
        }
        @media (max-width: 1080px) {
            .admin-menu-button { display: inline-flex; align-items: center; justify-content: center; }
            .admin-layout { display: block; }
            .admin-sidebar { position: fixed; z-index: 30; top: 68px; left: 0; width: min(320px, calc(100vw - 28px)); transform: translateX(-104%); transition: transform .22s ease; }
            body.admin-open .admin-sidebar { transform: translateX(0); }
            .admin-backdrop { position: fixed; inset: 68px 0 0; z-index: 20; background: rgba(17, 24, 39, .28); backdrop-filter: blur(6px); opacity: 0; pointer-events: none; transition: opacity .18s ease; display: block; }
            body.admin-open .admin-backdrop { opacity: 1; pointer-events: auto; }
        }
        @media (max-width: 780px) {
            .shell { width: min(100% - 24px, 1120px); }
            .topbar-inner { align-items: flex-start; flex-direction: column; padding: 12px 0; }
            .nav { width: 100%; justify-content: flex-start; overflow-x: auto; flex-wrap: nowrap; padding-bottom: 4px; }
            .nav a, .nav form, .nav button { flex: 0 0 auto; text-align: center; white-space: nowrap; }
            .booking-grid, .dashboard-grid, .settings-grid, .settings-tabs, .form-grid, .metric-row, .staff-card, .quick-actions, .diary-toolbar, .week-strip { grid-template-columns: 1fr; }
            .booking-card { grid-template-columns: 1fr; }
            .day-tile { min-height: auto; }
            .site-footer-inner { align-items: flex-start; flex-direction: column; }
            .powered-by img { height: 30px; max-width: 160px; }
            h1 { font-size: 42px; }
            .actions, .modal-actions { align-items: stretch; flex-direction: column; }
            .actions > *, .modal-actions > * { width: 100%; justify-content: center; }
            .admin-sidebar { top: 123px; height: calc(100vh - 123px); }
            .admin-backdrop { inset: 123px 0 0; }
        }
        .admin-shell {
            --ink: #26211a;
            --muted: #746b5d;
            --soft: #9b907d;
            --line: rgba(76, 58, 36, .16);
            --paper: #f4eee4;
            --panel: #fffaf1;
            --panel-soft: rgba(255, 250, 241, .86);
            --primary: #8f6935;
            --accent: #c49a53;
            --shadow-sm: 0 10px 26px rgba(34, 26, 16, .08);
            --shadow-md: 0 18px 48px rgba(34, 26, 16, .13);
            --shadow-lg: 0 30px 90px rgba(13, 11, 9, .32);
            min-height: 100vh;
            display: grid;
            grid-template-columns: 286px minmax(0, 1fr);
            background:
                radial-gradient(circle at top left, rgba(196, 154, 83, .18), transparent 34%),
                linear-gradient(135deg, #18130d 0%, #251d14 34%, #f3eadb 34%, #fbf7ef 100%);
            color: var(--ink);
        }
        html.dark .admin-shell {
            --ink: #fbf4e8;
            --muted: #c8bda9;
            --soft: #a99a82;
            --line: rgba(239, 220, 184, .16);
            --paper: #14110d;
            --panel: #1d1812;
            --panel-soft: rgba(29, 24, 18, .86);
            --primary: #d8b56d;
            --accent: #f0d08b;
            background:
                radial-gradient(circle at top left, rgba(216, 181, 109, .18), transparent 36%),
                linear-gradient(135deg, #090807 0%, #14100b 46%, #201810 100%);
        }
        .admin-shell::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image: linear-gradient(rgba(196, 154, 83, .055) 1px, transparent 1px), linear-gradient(90deg, rgba(196, 154, 83, .045) 1px, transparent 1px);
            background-size: 36px 36px;
            mask-image: linear-gradient(180deg, rgba(0,0,0,.7), transparent 76%);
        }
        .admin-shell .admin-sidebar {
            position: sticky;
            top: 0;
            z-index: 25;
            height: 100vh;
            padding: 18px;
            border-right: 1px solid rgba(239, 220, 184, .16);
            background: linear-gradient(180deg, rgba(13, 11, 9, .96), rgba(30, 23, 15, .92));
            color: #fbf4e8;
            box-shadow: 18px 0 48px rgba(13, 11, 9, .22);
            overflow-y: auto;
        }
        .admin-brand {
            display: grid;
            grid-template-columns: 46px minmax(0, 1fr);
            gap: 12px;
            align-items: center;
            min-height: 64px;
            padding: 10px;
            border: 1px solid rgba(239, 220, 184, .16);
            border-radius: 8px;
            color: #fff7e8;
            text-decoration: none;
            background: linear-gradient(135deg, rgba(196, 154, 83, .18), rgba(255,255,255,.035));
        }
        .admin-brand img, .admin-brand span {
            width: 46px;
            height: 46px;
            border-radius: 8px;
            object-fit: contain;
            background: #fffaf1;
            color: #15110c;
            display: grid;
            place-items: center;
            font-weight: 900;
        }
        .admin-brand strong { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .admin-shell .admin-nav {
            display: grid;
            gap: 8px;
            margin-top: 18px;
        }
        .admin-nav-section {
            border: 1px solid rgba(239, 220, 184, .12);
            border-radius: 8px;
            background: rgba(255,255,255,.035);
            overflow: hidden;
        }
        .admin-nav-section summary {
            min-height: 46px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            color: #e9dcc5;
            cursor: pointer;
            font-weight: 900;
            list-style: none;
        }
        .admin-nav-section summary::-webkit-details-marker { display: none; }
        .admin-nav-section[open] summary {
            color: #fff7e8;
            background: rgba(196, 154, 83, .12);
        }
        .admin-nav-items {
            display: grid;
            gap: 6px;
            padding: 8px;
        }
        .admin-shell .admin-nav a,
        .admin-nav-disabled {
            min-height: 42px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 12px;
            border: 1px solid transparent;
            border-radius: 8px;
            color: rgba(255, 247, 232, .72);
            text-decoration: none;
            font-weight: 800;
        }
        .admin-shell .admin-nav a:hover {
            color: #fff7e8;
            background: rgba(255,255,255,.07);
            transform: translateX(2px);
        }
        .admin-shell .admin-nav a.active {
            color: #15110c;
            background: linear-gradient(135deg, #f2d99d, #bd9050);
            border-color: rgba(255,255,255,.16);
            box-shadow: 0 12px 28px rgba(196, 154, 83, .24);
        }
        .admin-nav-disabled {
            opacity: .54;
            cursor: not-allowed;
        }
        .admin-nav-disabled small { font-size: 11px; text-transform: uppercase; letter-spacing: .06em; }
        .admin-shell .admin-main {
            position: relative;
            z-index: 1;
            min-width: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .admin-topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            min-height: 76px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 14px 26px;
            border-bottom: 1px solid var(--line);
            background: color-mix(in srgb, var(--panel) 86%, transparent);
            backdrop-filter: blur(18px) saturate(1.18);
            box-shadow: 0 10px 34px rgba(34, 26, 16, .08);
        }
        .admin-topbar-left,
        .admin-topbar-actions,
        .admin-profile-summary {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .admin-topbar-left strong { display: block; font-size: 18px; }
        .admin-kicker {
            color: var(--primary);
            display: block;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .11em;
            text-transform: uppercase;
        }
        .admin-icon-button {
            min-width: 44px;
            min-height: 44px;
            display: inline-grid;
            place-items: center;
            padding: 0 11px;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: color-mix(in srgb, var(--panel) 88%, white);
            color: var(--ink);
            box-shadow: var(--shadow-sm);
        }
        html.dark .admin-icon-button { background: rgba(255,255,255,.055); }
        .admin-menu-button { display: none; }
        .admin-dropdown {
            position: relative;
        }
        .admin-dropdown summary {
            list-style: none;
            cursor: pointer;
        }
        .admin-dropdown summary::-webkit-details-marker { display: none; }
        .admin-profile-summary {
            min-height: 44px;
            padding: 5px 10px 5px 5px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: color-mix(in srgb, var(--panel) 88%, white);
            box-shadow: var(--shadow-sm);
        }
        html.dark .admin-profile-summary { background: rgba(255,255,255,.055); }
        .admin-profile-summary span {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #15110c;
            font-weight: 950;
        }
        .admin-dropdown-panel {
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            width: min(300px, calc(100vw - 28px));
            z-index: 35;
            display: grid;
            gap: 10px;
            padding: 16px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
            box-shadow: var(--shadow-lg);
        }
        .admin-dropdown-panel p { margin: 0; }
        .admin-dropdown-panel a {
            color: var(--primary);
            font-weight: 850;
            text-decoration: none;
        }
        .admin-dropdown-panel .logout-form button {
            width: 100%;
        }
        .admin-content {
            flex: 1;
            padding: 0 0 26px;
        }
        .admin-shell .shell {
            width: min(1180px, calc(100% - 52px));
        }
        .admin-shell .hero {
            padding-top: 34px;
        }
        .admin-shell .panel,
        .admin-shell .metric,
        .admin-shell .booking-card,
        .admin-shell .staff-card,
        .admin-shell .day-tile,
        .admin-shell .settings-tabs,
        .admin-shell .settings-save {
            border-color: var(--line);
            background: var(--panel-soft);
            box-shadow: var(--shadow-sm);
        }
        .admin-shell .panel:hover,
        .admin-shell .metric:hover,
        .admin-shell .booking-card:hover,
        .admin-shell .staff-card:hover {
            box-shadow: var(--shadow-md);
        }
        .admin-shell input,
        .admin-shell select,
        .admin-shell textarea,
        .admin-shell .wysiwyg-editor,
        .admin-shell .editor-toolbar {
            border-color: var(--line);
            background: color-mix(in srgb, var(--panel) 92%, white);
            color: var(--ink);
        }
        html.dark .admin-shell input,
        html.dark .admin-shell select,
        html.dark .admin-shell textarea,
        html.dark .admin-shell .wysiwyg-editor,
        html.dark .admin-shell .editor-toolbar {
            background: rgba(255,255,255,.055);
        }
        .admin-shell .site-footer {
            border-top-color: var(--line);
            background: color-mix(in srgb, var(--panel) 82%, transparent);
        }
        @media (max-width: 1080px) {
            .admin-shell {
                display: block;
            }
            .admin-menu-button {
                display: inline-grid;
            }
            .admin-shell .admin-sidebar {
                position: fixed;
                inset: 0 auto 0 0;
                width: min(320px, calc(100vw - 28px));
                height: 100vh;
                transform: translateX(-104%);
                transition: transform .22s ease;
            }
            body.admin-open .admin-shell .admin-sidebar {
                transform: translateX(0);
            }
            .admin-shell .admin-backdrop {
                position: fixed;
                inset: 0;
                z-index: 22;
                display: block;
                background: rgba(13, 11, 9, .54);
                backdrop-filter: blur(8px);
                opacity: 0;
                pointer-events: none;
                transition: opacity .18s ease;
            }
            body.admin-open .admin-shell .admin-backdrop {
                opacity: 1;
                pointer-events: auto;
            }
        }
        @media (max-width: 780px) {
            .admin-topbar {
                align-items: flex-start;
                flex-direction: column;
                padding: 12px;
            }
            .admin-topbar-actions {
                width: 100%;
                justify-content: flex-start;
                overflow-x: auto;
                padding-bottom: 4px;
            }
            .admin-profile-summary strong { display: none; }
            .admin-shell .shell {
                width: min(100% - 24px, 1180px);
            }
        }
        .admin-shell {
            --ink: #2f2a22;
            --muted: #6f6659;
            --soft: #918676;
            --line: rgba(72, 58, 39, .13);
            --paper: #f7f1e8;
            --panel: rgba(255, 251, 244, .82);
            --panel-soft: rgba(255, 251, 244, .72);
            --primary: #9a753f;
            --accent: #d0a85f;
            --shadow-sm: 0 10px 28px rgba(40, 31, 20, .07);
            --shadow-md: 0 18px 48px rgba(40, 31, 20, .11);
            --shadow-lg: 0 30px 82px rgba(16, 13, 10, .25);
            background:
                radial-gradient(circle at 18% 0%, rgba(208, 168, 95, .14), transparent 34%),
                linear-gradient(180deg, #f7f1e8 0%, #f3ecdf 100%);
            font-size: 14px;
            line-height: 1.45;
        }
        html.dark .admin-shell {
            --ink: #f8efe0;
            --muted: #c7bda9;
            --soft: #a99b85;
            --line: rgba(245, 224, 187, .13);
            --paper: #13100c;
            --panel: rgba(28, 23, 17, .78);
            --panel-soft: rgba(28, 23, 17, .66);
            --primary: #d2ae66;
            --accent: #efd18c;
            background:
                radial-gradient(circle at 18% 0%, rgba(210, 174, 102, .13), transparent 34%),
                linear-gradient(180deg, #11100d 0%, #17120d 100%);
        }
        .admin-shell::before {
            opacity: .18;
            background-image: linear-gradient(rgba(154, 117, 63, .045) 1px, transparent 1px), linear-gradient(90deg, rgba(154, 117, 63, .035) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: linear-gradient(180deg, rgba(0,0,0,.55), transparent 74%);
        }
        .admin-shell .admin-sidebar {
            width: 286px;
            padding: 16px 14px;
            background: linear-gradient(180deg, rgba(24, 20, 15, .88), rgba(27, 22, 16, .8));
            border-right-color: rgba(245, 224, 187, .12);
            backdrop-filter: blur(24px) saturate(1.22);
            box-shadow: 14px 0 34px rgba(20, 15, 10, .18), inset -1px 0 rgba(255,255,255,.045);
        }
        .admin-brand {
            grid-template-columns: 38px minmax(0, 1fr);
            min-height: 56px;
            padding: 8px;
            background: rgba(255,255,255,.055);
            box-shadow: inset 0 1px rgba(255,255,255,.08);
        }
        .admin-brand img, .admin-brand span {
            width: 38px;
            height: 38px;
            border-radius: 8px;
        }
        .admin-brand strong {
            font-size: 13px;
            font-weight: 760;
            letter-spacing: 0;
        }
        .admin-shell .admin-nav {
            gap: 6px;
            margin-top: 14px;
        }
        .admin-nav-section {
            border-color: transparent;
            background: transparent;
        }
        .admin-nav-section summary {
            min-height: 40px;
            padding: 8px 10px;
            border: 1px solid transparent;
            border-radius: 8px;
            color: rgba(255, 247, 232, .72);
            font-size: 12.5px;
            font-weight: 720;
        }
        .admin-nav-section[open] summary {
            color: rgba(255, 247, 232, .96);
            background: rgba(255,255,255,.055);
            border-color: rgba(245, 224, 187, .08);
            box-shadow: inset 0 1px rgba(255,255,255,.07);
        }
        .admin-nav-title {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }
        .admin-nav-icon {
            width: 28px;
            height: 28px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(245, 224, 187, .09);
            border-radius: 8px;
            background: rgba(255,255,255,.045);
            color: rgba(245, 224, 187, .82);
            flex: 0 0 auto;
        }
        .admin-nav-icon svg,
        .admin-icon-button svg,
        .button svg,
        .admin-nav-chevron {
            width: 16px;
            height: 16px;
            display: block;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .admin-nav-chevron {
            width: 14px;
            height: 14px;
            opacity: .55;
            transition: transform .18s ease;
        }
        .admin-nav-section[open] .admin-nav-chevron {
            transform: rotate(180deg);
        }
        .admin-nav-items {
            gap: 3px;
            padding: 4px 6px 7px 47px;
        }
        .admin-shell .admin-nav a,
        .admin-nav-disabled {
            min-height: 34px;
            padding: 7px 10px;
            border-radius: 8px;
            color: rgba(255, 247, 232, .64);
            font-size: 12.5px;
            font-weight: 650;
        }
        .admin-shell .admin-nav a:hover {
            color: #fff7e8;
            background: rgba(255,255,255,.055);
            transform: translateX(1px);
        }
        .admin-shell .admin-nav a.active {
            color: #fff7e8;
            background: rgba(208, 168, 95, .18);
            border-color: rgba(208, 168, 95, .2);
            box-shadow: inset 0 1px rgba(255,255,255,.08), 0 8px 20px rgba(0,0,0,.12);
        }
        .admin-nav-disabled small {
            font-size: 10px;
            font-weight: 700;
            color: rgba(255, 247, 232, .42);
        }
        .admin-topbar {
            min-height: 66px;
            padding: 11px 24px;
            background: color-mix(in srgb, var(--panel) 78%, transparent);
            backdrop-filter: blur(28px) saturate(1.28);
            box-shadow: 0 12px 32px rgba(40, 31, 20, .06), inset 0 1px rgba(255,255,255,.45);
        }
        html.dark .admin-topbar {
            box-shadow: 0 12px 32px rgba(0,0,0,.18), inset 0 1px rgba(255,255,255,.055);
        }
        .admin-topbar-left,
        .admin-topbar-actions {
            gap: 9px;
        }
        .admin-topbar-left strong {
            font-size: 15px;
            font-weight: 760;
            line-height: 1.2;
        }
        .admin-kicker {
            font-size: 10px;
            font-weight: 750;
            letter-spacing: .09em;
        }
        .admin-shell .hero {
            padding-top: 28px;
            padding-bottom: 20px;
        }
        .admin-shell .hero.compact {
            padding-bottom: 10px;
        }
        .admin-shell .eyebrow {
            font-size: 10.5px;
            font-weight: 760;
            letter-spacing: .08em;
        }
        .admin-shell h1 {
            margin: 7px 0 8px;
            max-width: 760px;
            font-size: clamp(28px, 3.5vw, 44px);
            line-height: 1.05;
            font-weight: 780;
        }
        .admin-shell h2 {
            margin-bottom: 12px;
            font-size: 18px;
            line-height: 1.25;
            font-weight: 760;
        }
        .admin-shell h3 {
            margin-bottom: 6px;
            font-size: 14px;
            line-height: 1.3;
            font-weight: 740;
        }
        .admin-shell p {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.55;
        }
        .admin-shell .panel,
        .admin-shell .metric,
        .admin-shell .booking-card,
        .admin-shell .staff-card,
        .admin-shell .day-tile,
        .admin-shell .settings-tabs,
        .admin-shell .settings-save {
            border: 1px solid var(--line);
            border-radius: 12px;
            background: var(--panel-soft);
            backdrop-filter: blur(26px) saturate(1.2);
            box-shadow: var(--shadow-sm), inset 0 1px rgba(255,255,255,.48);
        }
        html.dark .admin-shell .panel,
        html.dark .admin-shell .metric,
        html.dark .admin-shell .booking-card,
        html.dark .admin-shell .staff-card,
        html.dark .admin-shell .day-tile,
        html.dark .admin-shell .settings-tabs,
        html.dark .admin-shell .settings-save {
            box-shadow: var(--shadow-sm), inset 0 1px rgba(255,255,255,.055);
        }
        .admin-shell .panel {
            padding: 18px;
        }
        .admin-shell .panel:hover,
        .admin-shell .metric:hover,
        .admin-shell .booking-card:hover,
        .admin-shell .staff-card:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md), inset 0 1px rgba(255,255,255,.5);
        }
        html.dark .admin-shell .panel:hover,
        html.dark .admin-shell .metric:hover,
        html.dark .admin-shell .booking-card:hover,
        html.dark .admin-shell .staff-card:hover {
            box-shadow: var(--shadow-md), inset 0 1px rgba(255,255,255,.06);
        }
        .admin-shell .metric {
            padding: 14px;
        }
        .admin-shell .metric strong {
            margin-top: 4px;
            font-size: 24px;
            line-height: 1.05;
            font-weight: 780;
        }
        .admin-shell .metric span,
        .admin-shell label {
            color: var(--soft);
            font-size: 11.5px;
            font-weight: 720;
            letter-spacing: .02em;
        }
        .admin-shell .booking-card {
            grid-template-columns: 96px minmax(0, 1fr) auto;
            gap: 12px;
            border-left-width: 3px;
            padding: 13px;
        }
        .admin-shell .booking-time strong {
            font-size: 19px;
            font-weight: 780;
        }
        .admin-shell .staff-card {
            padding: 13px;
        }
        .admin-shell .badge {
            padding: 4px 8px;
            border-color: color-mix(in srgb, var(--line) 72%, transparent);
            background: color-mix(in srgb, var(--panel) 76%, transparent);
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
            line-height: 1.2;
            box-shadow: inset 0 1px rgba(255,255,255,.4);
        }
        html.dark .admin-shell .badge {
            box-shadow: inset 0 1px rgba(255,255,255,.045);
        }
        .admin-shell .button,
        .admin-shell button,
        .admin-shell .nav a {
            min-height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 8px 12px;
            border-radius: 9px;
            border-color: var(--line);
            background: color-mix(in srgb, var(--panel) 72%, transparent);
            color: var(--ink);
            font-size: 12.5px;
            font-weight: 720;
            line-height: 1;
            box-shadow: 0 7px 18px rgba(40, 31, 20, .055), inset 0 1px rgba(255,255,255,.48);
        }
        html.dark .admin-shell .button,
        html.dark .admin-shell button,
        html.dark .admin-shell .nav a {
            box-shadow: 0 7px 18px rgba(0,0,0,.18), inset 0 1px rgba(255,255,255,.055);
        }
        .admin-shell .button:hover,
        .admin-shell button:hover {
            border-color: color-mix(in srgb, var(--primary) 34%, var(--line));
            background: color-mix(in srgb, var(--panel) 86%, white);
            transform: translateY(-1px);
        }
        html.dark .admin-shell .button:hover,
        html.dark .admin-shell button:hover {
            background: rgba(255,255,255,.08);
        }
        .admin-shell .button.primary,
        .admin-shell button.primary {
            border-color: color-mix(in srgb, var(--primary) 58%, transparent);
            background: linear-gradient(180deg, color-mix(in srgb, var(--accent) 82%, white), var(--primary));
            color: #1d160d;
            box-shadow: 0 11px 24px color-mix(in srgb, var(--primary) 22%, transparent), inset 0 1px rgba(255,255,255,.38);
        }
        .admin-shell .button.subtle,
        .admin-shell button.subtle,
        .admin-shell .button.secondary {
            background: color-mix(in srgb, var(--panel) 78%, transparent);
            color: var(--primary);
            border-color: color-mix(in srgb, var(--primary) 22%, var(--line));
        }
        .admin-shell .button.ghost,
        .admin-shell button.ghost {
            background: transparent;
            box-shadow: none;
        }
        .admin-icon-button {
            min-width: 38px;
            width: 38px;
            min-height: 38px;
            padding: 0;
            border-radius: 10px;
            background: color-mix(in srgb, var(--panel) 72%, transparent);
        }
        .admin-profile-summary {
            min-height: 38px;
            padding: 4px 10px 4px 4px;
            border-radius: 11px;
            background: color-mix(in srgb, var(--panel) 74%, transparent);
            box-shadow: 0 7px 18px rgba(40, 31, 20, .055), inset 0 1px rgba(255,255,255,.5);
        }
        html.dark .admin-profile-summary {
            background: rgba(255,255,255,.055);
            box-shadow: 0 7px 18px rgba(0,0,0,.18), inset 0 1px rgba(255,255,255,.055);
        }
        .admin-profile-summary span {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 780;
        }
        .admin-profile-summary strong {
            font-size: 12.5px;
            font-weight: 720;
        }
        .admin-dropdown-panel {
            padding: 14px;
            border-radius: 12px;
            background: var(--panel);
            backdrop-filter: blur(26px) saturate(1.2);
            box-shadow: var(--shadow-lg), inset 0 1px rgba(255,255,255,.48);
        }
        html.dark .admin-dropdown-panel {
            box-shadow: var(--shadow-lg), inset 0 1px rgba(255,255,255,.055);
        }
        .admin-shell input,
        .admin-shell select,
        .admin-shell textarea,
        .admin-shell .wysiwyg-editor {
            min-height: 40px;
            padding: 9px 11px;
            border-radius: 9px;
            background: color-mix(in srgb, var(--panel) 72%, white);
            font-size: 13px;
        }
        .admin-shell .editor-toolbar {
            border-radius: 9px 9px 0 0;
            background: color-mix(in srgb, var(--panel) 70%, transparent);
        }
        .admin-shell .settings-tabs {
            gap: 6px;
            padding: 6px;
        }
        .admin-shell .settings-tab {
            min-height: 38px;
            font-size: 12px;
        }
        .admin-shell .day-tile {
            min-height: 82px;
            padding: 10px;
        }
        .admin-shell .day-tile strong {
            font-size: 17px;
            font-weight: 760;
        }
        .admin-shell .day-tile span {
            font-size: 11px;
            font-weight: 700;
        }
        .admin-shell .empty-state,
        .admin-shell .notice,
        .admin-shell .success,
        .admin-shell .errors {
            border-radius: 12px;
            background: color-mix(in srgb, var(--panel) 72%, transparent);
            box-shadow: inset 0 1px rgba(255,255,255,.42);
        }
        .admin-shell .success,
        .admin-shell .success p {
            color: #17633c;
        }
        html.dark .admin-shell .success,
        html.dark .admin-shell .success p {
            color: #96e4b7;
        }
        .admin-shell .errors,
        .admin-shell .errors p,
        .admin-shell .error {
            color: #9c2020;
        }
        html.dark .admin-shell .errors,
        html.dark .admin-shell .errors p,
        html.dark .admin-shell .error {
            color: #ffb4b4;
        }
        @media (max-width: 780px) {
            .admin-shell h1 {
                font-size: 30px;
            }
            .admin-shell .booking-card,
            .admin-shell .staff-card {
                grid-template-columns: 1fr;
            }
            .admin-topbar-actions .button.primary {
                flex: 0 0 auto;
            }
        }
        .is-public {
            --ink: #28231c;
            --muted: #6e665b;
            --soft: #918675;
            --line: rgba(72, 58, 39, .14);
            --paper: #f7f1e8;
            --panel: rgba(255, 251, 244, .84);
            --panel-soft: rgba(255, 251, 244, .72);
            --primary: color-mix(in srgb, {{ $venue->primary_colour ?? '#8f6935' }} 58%, #7a5a32);
            --accent: color-mix(in srgb, {{ $venue->accent_colour ?? '#c59b5b' }} 72%, #d0a85f);
            --shadow-sm: 0 12px 32px rgba(40, 31, 20, .08);
            --shadow-md: 0 20px 56px rgba(40, 31, 20, .13);
            --shadow-lg: 0 34px 96px rgba(40, 31, 20, .2);
            --focus: 0 0 0 4px color-mix(in srgb, var(--accent) 22%, transparent);
            background:
                radial-gradient(circle at 16% 0%, color-mix(in srgb, var(--accent) 20%, transparent), transparent 34%),
                radial-gradient(circle at 88% 12%, color-mix(in srgb, var(--primary) 12%, transparent), transparent 30%),
                linear-gradient(180deg, #fffaf1 0%, #f7f1e8 48%, #efe5d5 100%);
        }
        .is-public::before {
            opacity: .5;
            background-image: linear-gradient(rgba(72, 58, 39, .035) 1px, transparent 1px), linear-gradient(90deg, rgba(72, 58, 39, .025) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: linear-gradient(180deg, rgba(0,0,0,.55), transparent 74%);
        }
        .is-public .topbar {
            background: rgba(255, 251, 244, .72);
            border-bottom-color: var(--line);
            backdrop-filter: blur(26px) saturate(1.24);
            box-shadow: 0 14px 36px rgba(40,31,20,.07), inset 0 1px rgba(255,255,255,.62);
        }
        .is-public .brand strong {
            color: var(--ink);
            font-size: 16px;
            font-weight: 760;
        }
        .is-public .brand span {
            color: var(--muted);
        }
        .is-public .brand-logo {
            border-color: rgba(72,58,39,.12);
            background: #fffaf1;
            box-shadow: 0 10px 24px rgba(40,31,20,.09);
        }
        .is-public .nav a,
        .is-public .nav button,
        .is-public .button,
        .is-public button {
            min-height: 40px;
            border-color: var(--line);
            border-radius: 10px;
            background: rgba(255,255,255,.64);
            color: var(--ink);
            padding: 9px 13px;
            font-size: 13px;
            font-weight: 720;
            box-shadow: 0 8px 22px rgba(40,31,20,.055), inset 0 1px rgba(255,255,255,.72);
        }
        .is-public .nav a:hover,
        .is-public .nav button:hover,
        .is-public .button:hover,
        .is-public button:hover {
            border-color: color-mix(in srgb, var(--accent) 34%, var(--line));
            background: rgba(255,255,255,.86);
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm), inset 0 1px rgba(255,255,255,.82);
        }
        .is-public .button.primary,
        .is-public button.primary {
            border-color: color-mix(in srgb, var(--accent) 54%, transparent);
            background: linear-gradient(180deg, color-mix(in srgb, var(--accent) 88%, white), color-mix(in srgb, var(--primary) 82%, #8a6535));
            color: #18120b;
            box-shadow: 0 14px 32px color-mix(in srgb, var(--accent) 24%, transparent), inset 0 1px rgba(255,255,255,.35);
        }
        .is-public .button.subtle,
        .is-public button.subtle {
            background: rgba(255,255,255,.64);
            color: var(--primary);
            border-color: color-mix(in srgb, var(--accent) 28%, var(--line));
        }
        .is-public .button.danger,
        .is-public button.danger {
            background: #fff7f7;
            color: #a62929;
            border-color: rgba(166, 41, 41, .2);
        }
        .is-public .hero {
            padding: 54px 0 30px;
        }
        .is-public .hero.compact {
            padding-bottom: 18px;
        }
        .is-public .eyebrow {
            color: var(--primary);
            font-size: 11px;
            font-weight: 760;
            letter-spacing: .09em;
        }
        .is-public h1 {
            max-width: 860px;
            color: var(--ink);
            font-size: clamp(38px, 6vw, 68px);
            line-height: .98;
            font-weight: 780;
        }
        .is-public h2 {
            color: var(--ink);
            font-size: 20px;
            font-weight: 760;
        }
        .is-public h3 {
            color: #3a3023;
            font-size: 15px;
            font-weight: 740;
        }
        .is-public p {
            color: var(--muted);
        }
        .is-public .panel,
        .is-public .notice,
        .is-public .empty-state,
        .is-public .success,
        .is-public .errors {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: var(--panel-soft);
            backdrop-filter: blur(26px) saturate(1.22);
            box-shadow: var(--shadow-sm), inset 0 1px rgba(255,255,255,.68);
        }
        .is-public .panel {
            padding: 22px;
        }
        .is-public .panel:hover {
            box-shadow: var(--shadow-md), inset 0 1px rgba(255,255,255,.78);
        }
        .is-public label {
            color: #4b4033;
            font-size: 12px;
            font-weight: 720;
        }
        .is-public input,
        .is-public select,
        .is-public textarea {
            min-height: 42px;
            border-color: var(--line);
            border-radius: 10px;
            background: rgba(255,255,255,.76);
            color: var(--ink);
            padding: 10px 12px;
            box-shadow: inset 0 1px rgba(255,255,255,.76);
        }
        .is-public input:hover,
        .is-public select:hover,
        .is-public textarea:hover {
            border-color: color-mix(in srgb, var(--accent) 28%, var(--line));
            background: rgba(255,255,255,.92);
        }
        .is-public input::placeholder,
        .is-public textarea::placeholder {
            color: rgba(40,35,28,.44);
        }
        .is-public .slot span {
            min-height: 42px;
            border-color: var(--line);
            border-radius: 10px;
            background: rgba(255,255,255,.68);
            color: var(--ink);
            font-size: 13px;
            font-weight: 760;
            box-shadow: inset 0 1px rgba(255,255,255,.72);
        }
        .is-public .slot:hover span {
            border-color: color-mix(in srgb, var(--accent) 36%, var(--line));
            box-shadow: var(--shadow-sm), inset 0 1px rgba(255,255,255,.82);
        }
        .is-public .slot input:checked + span {
            border-color: var(--accent);
            background: color-mix(in srgb, var(--accent) 18%, white);
            color: var(--primary);
            box-shadow: 0 12px 28px color-mix(in srgb, var(--accent) 18%, transparent);
        }
        .is-public .badge {
            border-color: var(--line);
            background: rgba(255,255,255,.7);
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
            box-shadow: inset 0 1px rgba(255,255,255,.72);
        }
        .is-public .notice {
            background: color-mix(in srgb, var(--accent) 12%, white);
            color: #4b3924;
        }
        .is-public .notice strong,
        .is-public .empty-state strong {
            color: var(--ink);
        }
        .is-public .success,
        .is-public .success p {
            color: #17633c;
        }
        .is-public .errors,
        .is-public .errors p,
        .is-public .error {
            color: #9c2020;
        }
        .is-public .site-footer {
            border-top-color: var(--line);
            background: rgba(255, 251, 244, .7);
            backdrop-filter: blur(22px) saturate(1.15);
        }
        .is-public .powered-by,
        .is-public .site-footer small {
            color: var(--muted);
        }
        .is-public .modal {
            border-color: var(--line);
            background: rgba(255, 251, 244, .94);
            color: var(--ink);
            backdrop-filter: blur(26px) saturate(1.2);
        }
        @media (max-width: 780px) {
            .is-public h1 {
                font-size: 40px;
            }
            .is-public .hero {
                padding-top: 38px;
            }
            .is-public .panel {
                padding: 18px;
            }
        }
        .is-public,
        .admin-shell {
            --gold: #b98a42;
            --gold-soft: rgba(185, 138, 66, .18);
            --glass-cream: rgba(255, 251, 244, .7);
            --glass-cream-strong: rgba(255, 251, 244, .86);
            --glass-border: rgba(116, 91, 53, .16);
            --glass-highlight: rgba(255, 255, 255, .72);
        }
        html.dark .admin-shell {
            --gold: #d2ae66;
            --gold-soft: rgba(210, 174, 102, .18);
            --glass-cream: rgba(34, 28, 20, .68);
            --glass-cream-strong: rgba(39, 32, 23, .84);
            --glass-border: rgba(245, 224, 187, .14);
            --glass-highlight: rgba(255, 255, 255, .07);
        }
        .is-public .panel,
        .is-public .notice,
        .is-public .empty-state,
        .is-public .modal,
        .admin-shell .panel,
        .admin-shell .metric,
        .admin-shell .booking-card,
        .admin-shell .staff-card,
        .admin-shell .day-tile,
        .admin-shell .settings-tabs,
        .admin-shell .settings-save,
        .admin-shell .notice,
        .admin-shell .empty-state,
        .admin-shell .modal,
        .admin-dropdown-panel {
            position: relative;
            overflow: hidden;
            border-color: var(--glass-border);
            background: color-mix(in srgb, var(--glass-cream) 92%, transparent);
            backdrop-filter: blur(30px) saturate(1.22);
            box-shadow:
                0 18px 48px rgba(40, 31, 20, .1),
                inset 0 1px var(--glass-highlight),
                inset 0 -1px rgba(116, 91, 53, .055);
            transition: transform .22s cubic-bezier(.2,.8,.2,1), border-color .22s ease, box-shadow .22s ease, background .22s ease;
        }
        html.dark .admin-shell .panel,
        html.dark .admin-shell .metric,
        html.dark .admin-shell .booking-card,
        html.dark .admin-shell .staff-card,
        html.dark .admin-shell .day-tile,
        html.dark .admin-shell .settings-tabs,
        html.dark .admin-shell .settings-save,
        html.dark .admin-shell .notice,
        html.dark .admin-shell .empty-state,
        html.dark .admin-shell .modal,
        html.dark .admin-dropdown-panel {
            box-shadow:
                0 18px 48px rgba(0, 0, 0, .2),
                inset 0 1px var(--glass-highlight),
                inset 0 -1px rgba(255, 255, 255, .035);
        }
        .is-public .panel::before,
        .is-public .notice::before,
        .is-public .empty-state::before,
        .admin-shell .panel::before,
        .admin-shell .metric::before,
        .admin-shell .booking-card::before,
        .admin-shell .staff-card::before,
        .admin-shell .day-tile::before,
        .admin-shell .settings-tabs::before,
        .admin-shell .notice::before,
        .admin-shell .empty-state::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at 18% 0%, rgba(255,255,255,.64), transparent 28%),
                radial-gradient(circle at 86% 8%, var(--gold-soft), transparent 26%);
            opacity: .7;
            transition: opacity .22s ease, transform .22s ease;
        }
        html.dark .admin-shell .panel::before,
        html.dark .admin-shell .metric::before,
        html.dark .admin-shell .booking-card::before,
        html.dark .admin-shell .staff-card::before,
        html.dark .admin-shell .day-tile::before,
        html.dark .admin-shell .settings-tabs::before,
        html.dark .admin-shell .notice::before,
        html.dark .admin-shell .empty-state::before {
            background:
                radial-gradient(circle at 18% 0%, rgba(255,255,255,.08), transparent 28%),
                radial-gradient(circle at 86% 8%, var(--gold-soft), transparent 26%);
        }
        .is-public .panel:hover,
        .admin-shell .panel:hover,
        .admin-shell .metric:hover,
        .admin-shell .booking-card:hover,
        .admin-shell .staff-card:hover,
        .admin-shell .day-tile:hover {
            transform: translateY(-2px);
            border-color: color-mix(in srgb, var(--gold) 34%, var(--glass-border));
            background: var(--glass-cream-strong);
            box-shadow:
                0 24px 64px rgba(40, 31, 20, .14),
                0 0 0 1px rgba(255,255,255,.34) inset,
                inset 0 1px var(--glass-highlight);
        }
        html.dark .admin-shell .panel:hover,
        html.dark .admin-shell .metric:hover,
        html.dark .admin-shell .booking-card:hover,
        html.dark .admin-shell .staff-card:hover,
        html.dark .admin-shell .day-tile:hover {
            box-shadow:
                0 24px 64px rgba(0, 0, 0, .3),
                0 0 0 1px rgba(255,255,255,.05) inset,
                inset 0 1px var(--glass-highlight);
        }
        .is-public .panel:hover::before,
        .admin-shell .panel:hover::before,
        .admin-shell .metric:hover::before,
        .admin-shell .booking-card:hover::before,
        .admin-shell .staff-card:hover::before,
        .admin-shell .day-tile:hover::before {
            opacity: .95;
            transform: translate3d(0, -2px, 0) scale(1.02);
        }
        .is-public .button,
        .is-public button,
        .is-public .nav a,
        .admin-shell .button,
        .admin-shell button,
        .admin-shell .admin-icon-button,
        .admin-shell .admin-profile-summary {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            border: 1px solid var(--glass-border);
            border-radius: 11px;
            background: rgba(255, 251, 244, .62);
            color: var(--ink);
            box-shadow:
                0 10px 24px rgba(40, 31, 20, .075),
                inset 0 1px rgba(255,255,255,.74),
                inset 0 -1px rgba(116, 91, 53, .06);
            transition: transform .18s cubic-bezier(.2,.8,.2,1), border-color .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease;
        }
        html.dark .admin-shell .button,
        html.dark .admin-shell button,
        html.dark .admin-shell .admin-icon-button,
        html.dark .admin-shell .admin-profile-summary {
            background: rgba(255, 251, 244, .06);
            color: var(--ink);
            box-shadow:
                0 10px 24px rgba(0, 0, 0, .2),
                inset 0 1px rgba(255,255,255,.055),
                inset 0 -1px rgba(255,255,255,.025);
        }
        .is-public .button::before,
        .is-public button::before,
        .is-public .nav a::before,
        .admin-shell .button::before,
        .admin-shell button::before,
        .admin-shell .admin-icon-button::before,
        .admin-shell .admin-profile-summary::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background:
                radial-gradient(circle at 24% 0%, rgba(255,255,255,.78), transparent 32%),
                radial-gradient(circle at 82% 100%, var(--gold-soft), transparent 34%);
            opacity: .78;
            transition: transform .22s ease, opacity .22s ease;
        }
        html.dark .admin-shell .button::before,
        html.dark .admin-shell button::before,
        html.dark .admin-shell .admin-icon-button::before,
        html.dark .admin-shell .admin-profile-summary::before {
            background:
                radial-gradient(circle at 24% 0%, rgba(255,255,255,.09), transparent 32%),
                radial-gradient(circle at 82% 100%, var(--gold-soft), transparent 34%);
        }
        .is-public .button:hover,
        .is-public button:hover,
        .is-public .nav a:hover,
        .admin-shell .button:hover,
        .admin-shell button:hover,
        .admin-shell .admin-icon-button:hover,
        .admin-shell .admin-profile-summary:hover {
            transform: translateY(-1px);
            border-color: color-mix(in srgb, var(--gold) 38%, var(--glass-border));
            background: rgba(255, 251, 244, .82);
            color: var(--ink);
            box-shadow:
                0 16px 34px rgba(40, 31, 20, .11),
                inset 0 1px rgba(255,255,255,.86),
                0 0 0 3px color-mix(in srgb, var(--gold) 10%, transparent);
        }
        html.dark .admin-shell .button:hover,
        html.dark .admin-shell button:hover,
        html.dark .admin-shell .admin-icon-button:hover,
        html.dark .admin-shell .admin-profile-summary:hover {
            background: rgba(255, 251, 244, .09);
            box-shadow:
                0 16px 34px rgba(0, 0, 0, .28),
                inset 0 1px rgba(255,255,255,.075),
                0 0 0 3px color-mix(in srgb, var(--gold) 12%, transparent);
        }
        .is-public .button:hover::before,
        .is-public button:hover::before,
        .is-public .nav a:hover::before,
        .admin-shell .button:hover::before,
        .admin-shell button:hover::before,
        .admin-shell .admin-icon-button:hover::before,
        .admin-shell .admin-profile-summary:hover::before {
            opacity: 1;
            transform: translate3d(0, -1px, 0) scale(1.08);
        }
        .is-public .button:active,
        .is-public button:active,
        .is-public .nav a:active,
        .admin-shell .button:active,
        .admin-shell button:active,
        .admin-shell .admin-icon-button:active,
        .admin-shell .admin-profile-summary:active {
            transform: translateY(0) scale(.985);
            box-shadow:
                0 8px 18px rgba(40, 31, 20, .08),
                inset 0 2px 5px rgba(116, 91, 53, .12);
        }
        .is-public .button.primary,
        .is-public button.primary,
        .admin-shell .button.primary,
        .admin-shell button.primary {
            border-color: color-mix(in srgb, var(--gold) 44%, var(--glass-border));
            background: color-mix(in srgb, #fff8ea 82%, var(--gold) 18%);
            color: #312617;
            box-shadow:
                0 14px 30px color-mix(in srgb, var(--gold) 16%, transparent),
                inset 0 1px rgba(255,255,255,.84),
                inset 0 -1px rgba(116, 91, 53, .08);
        }
        .is-public .button.primary::after,
        .is-public button.primary::after,
        .admin-shell .button.primary::after,
        .admin-shell button.primary::after {
            content: "";
            position: absolute;
            left: 12px;
            right: 12px;
            bottom: 6px;
            height: 1px;
            background: color-mix(in srgb, var(--gold) 56%, transparent);
            opacity: .6;
        }
        .is-public .button.subtle,
        .is-public button.subtle,
        .admin-shell .button.subtle,
        .admin-shell button.subtle,
        .admin-shell .button.secondary {
            color: color-mix(in srgb, var(--gold) 72%, var(--ink));
            background: rgba(255, 251, 244, .54);
            border-color: color-mix(in srgb, var(--gold) 24%, var(--glass-border));
        }
        .is-public .slot span,
        .admin-shell .slot span,
        .is-public .badge,
        .admin-shell .badge,
        .admin-nav-section summary {
            border-color: var(--glass-border);
            background: color-mix(in srgb, var(--glass-cream) 86%, transparent);
            box-shadow: inset 0 1px var(--glass-highlight);
            transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }
        .is-public .slot:hover span,
        .admin-shell .slot:hover span,
        .admin-nav-section summary:hover {
            transform: translateY(-1px);
            border-color: color-mix(in srgb, var(--gold) 34%, var(--glass-border));
            background: var(--glass-cream-strong);
            box-shadow: 0 12px 28px rgba(40, 31, 20, .1), inset 0 1px var(--glass-highlight);
        }
        .is-public .slot input:checked + span,
        .admin-shell .slot input:checked + span,
        .admin-nav-section[open] summary,
        .admin-shell .admin-nav a.active,
        .settings-tab.active {
            border-color: color-mix(in srgb, var(--gold) 46%, var(--glass-border));
            background: color-mix(in srgb, var(--gold) 13%, var(--glass-cream-strong));
            color: color-mix(in srgb, var(--gold) 70%, var(--ink));
            box-shadow:
                0 14px 30px color-mix(in srgb, var(--gold) 13%, transparent),
                inset 0 1px var(--glass-highlight);
        }
        .admin-topbar {
            overflow: visible;
        }
        .admin-topbar .admin-dropdown {
            position: relative;
            flex: 0 0 auto;
        }
        .admin-topbar .admin-dropdown-panel {
            position: absolute;
            top: calc(100% + 12px);
            right: 0;
            z-index: 80;
            width: min(320px, calc(100vw - 28px));
            transform-origin: top right;
            animation: dropdownFloatIn .16s ease both;
        }
        .admin-topbar .admin-dropdown-panel.profile {
            width: min(340px, calc(100vw - 28px));
        }
        @keyframes dropdownFloatIn {
            from {
                opacity: 0;
                transform: translate3d(0, -4px, 0) scale(.98);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0) scale(1);
            }
        }
        .is-public::after,
        .admin-shell::after {
            content: "";
            position: fixed;
            inset: -34% -28%;
            z-index: 0;
            pointer-events: none;
            opacity: .88;
            background:
                linear-gradient(118deg, transparent 0 16%, rgba(255,255,255,.82) 21%, rgba(245, 222, 181, .5) 27%, transparent 35% 100%),
                linear-gradient(132deg, transparent 0 30%, rgba(223, 184, 116, .46) 36%, rgba(255, 248, 232, .72) 43%, transparent 54% 100%),
                linear-gradient(104deg, transparent 0 52%, rgba(237, 206, 156, .44) 58%, rgba(255,255,255,.58) 64%, transparent 74% 100%),
                radial-gradient(38% 32% at 14% 18%, rgba(255,255,255,.9), transparent 68%),
                radial-gradient(32% 30% at 76% 12%, color-mix(in srgb, var(--gold) 34%, transparent), transparent 70%),
                conic-gradient(from 132deg at 52% 45%, transparent 0 20%, rgba(255,255,255,.42) 32%, color-mix(in srgb, var(--gold) 24%, transparent) 48%, transparent 66% 100%);
            background-size: 120% 120%, 132% 132%, 116% 116%, 100% 100%, 100% 100%, 100% 100%;
            filter: blur(22px) saturate(1.14);
            transform: translate3d(0,0,0);
            animation: ambientGlass 18s ease-in-out infinite alternate;
        }
        .is-public {
            background:
                radial-gradient(circle at 10% 8%, rgba(255,255,255,.9), transparent 30%),
                radial-gradient(circle at 82% 18%, color-mix(in srgb, var(--gold) 18%, transparent), transparent 34%),
                radial-gradient(circle at 48% 92%, rgba(230, 199, 151, .28), transparent 36%),
                linear-gradient(180deg, #fff9ef 0%, #f7f1e8 48%, #efe4d2 100%);
        }
        .admin-shell {
            background:
                radial-gradient(circle at 12% 10%, rgba(255,255,255,.82), transparent 28%),
                radial-gradient(circle at 78% 16%, rgba(196, 154, 83, .28), transparent 34%),
                radial-gradient(circle at 44% 86%, rgba(231, 194, 139, .24), transparent 38%),
                linear-gradient(180deg, #fff9ef 0%, #f6efe3 48%, #efe4d2 100%);
        }
        html.dark .admin-shell::after {
            opacity: .46;
            background:
                linear-gradient(118deg, transparent 0 16%, rgba(255,255,255,.12) 21%, rgba(245, 222, 181, .12) 27%, transparent 35% 100%),
                linear-gradient(132deg, transparent 0 30%, rgba(223, 184, 116, .2) 36%, rgba(255, 248, 232, .12) 43%, transparent 54% 100%),
                linear-gradient(104deg, transparent 0 52%, rgba(237, 206, 156, .16) 58%, rgba(255,255,255,.1) 64%, transparent 74% 100%),
                radial-gradient(38% 32% at 14% 18%, rgba(255,255,255,.12), transparent 68%),
                radial-gradient(32% 30% at 76% 12%, color-mix(in srgb, var(--gold) 24%, transparent), transparent 70%),
                conic-gradient(from 132deg at 52% 45%, transparent 0 20%, rgba(255,255,255,.08) 32%, color-mix(in srgb, var(--gold) 14%, transparent) 48%, transparent 66% 100%);
        }
        html.dark .admin-shell {
            background:
                radial-gradient(circle at 10% 8%, rgba(216, 181, 109, .16), transparent 30%),
                radial-gradient(circle at 78% 18%, rgba(255,255,255,.06), transparent 34%),
                radial-gradient(circle at 46% 88%, rgba(196, 154, 83, .12), transparent 38%),
                linear-gradient(180deg, #15110d 0%, #1d1710 48%, #110e0a 100%);
        }
        .is-public > :not(.modal-backdrop),
        .admin-shell > * {
            position: relative;
            z-index: 1;
        }
        .is-public > .modal-backdrop {
            z-index: 40;
        }
        @keyframes ambientGlass {
            0% {
                background-position: 0% 12%, 100% 0%, 18% 100%, 0 0, 0 0, 0 0;
                transform: translate3d(-3%, -2%, 0) rotate(-4deg) scale(1.02);
            }
            50% {
                background-position: 28% 4%, 72% 18%, 44% 78%, 0 0, 0 0, 0 0;
                transform: translate3d(2.5%, 2%, 0) rotate(3deg) scale(1.07);
            }
            100% {
                background-position: 46% 18%, 48% 34%, 72% 58%, 0 0, 0 0, 0 0;
                transform: translate3d(4%, -2.5%, 0) rotate(-2deg) scale(1.1);
            }
        }
        @media (prefers-reduced-motion: reduce) {
            .is-public::after,
            .admin-shell::after,
            .admin-topbar .admin-dropdown-panel {
                animation: none;
            }
        }
    </style>
</head>
@php($isAdminArea = auth()->check() && request()->routeIs('admin.*'))
<body class="{{ $isAdminArea ? 'is-admin' : 'is-public' }}">

    @if ($isAdminArea)
        @include('layouts.partials.admin-shell')
    @else
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
                    <a href="{{ route('bookings.lookup') }}">Manage booking</a>
                    @auth
                        <a href="{{ route('admin.dashboard') }}">Admin</a>
                        <form class="logout-form" method="post" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit">Log out</button>
                        </form>
                    @else
                        <a href="{{ route('signup') }}">Start trial</a>
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
    @endif
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
            const adminMenuButton = document.querySelector('[data-admin-menu]');
            const adminBackdrop = document.querySelector('[data-admin-backdrop]');
            const modal = document.querySelector('[data-confirm-modal]');
            const message = document.querySelector('#confirm-message');
            const cancelButton = document.querySelector('[data-modal-cancel]');
            const confirmButton = document.querySelector('[data-modal-confirm]');
            const themeToggle = document.querySelector('[data-theme-toggle]');
            let pendingForm = null;

            const closeAdminMenu = () => {
                document.body.classList.remove('admin-open');
                adminMenuButton?.setAttribute('aria-expanded', 'false');
            };

            adminMenuButton?.addEventListener('click', () => {
                const isOpen = document.body.classList.toggle('admin-open');
                adminMenuButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
            adminBackdrop?.addEventListener('click', closeAdminMenu);
            document.querySelectorAll('.admin-nav a').forEach((link) => {
                link.addEventListener('click', closeAdminMenu);
            });

            themeToggle?.addEventListener('click', () => {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('restaurant-admin-theme', isDark ? 'dark' : 'light');
            });

            document.querySelectorAll('[data-editor]').forEach((editor) => {
                const textarea = document.querySelector(editor.dataset.editor);
                const toolbar = editor.previousElementSibling;
                const sync = () => {
                    textarea.value = editor.innerHTML.trim();
                };

                toolbar?.querySelectorAll('[data-editor-command]').forEach((button) => {
                    button.addEventListener('click', () => {
                        document.execCommand(button.dataset.editorCommand, false, null);
                        editor.focus();
                        sync();
                    });
                });

                editor.addEventListener('input', sync);
                editor.closest('form')?.addEventListener('submit', sync);
            });

            const settingsTabs = document.querySelectorAll('[data-settings-tab]');
            const settingsPanels = document.querySelectorAll('[data-settings-panel]');
            settingsTabs.forEach((tab) => {
                tab.addEventListener('click', () => {
                    const target = tab.dataset.settingsTab;

                    settingsTabs.forEach((item) => {
                        const isActive = item === tab;
                        item.classList.toggle('active', isActive);
                        item.setAttribute('aria-selected', isActive ? 'true' : 'false');
                    });

                    settingsPanels.forEach((panel) => {
                        panel.toggleAttribute('hidden', panel.dataset.settingsPanel !== target);
                    });
                });
            });

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
                if (event.key === 'Escape') {
                    closeAdminMenu();
                }
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
