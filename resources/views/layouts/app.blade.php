<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ($title ?? null) ? $title.' · Resora OS' : 'Resora OS' }}</title>
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
            --gold: #7367f0;
            --gold-soft: rgba(115, 103, 240, .14);
            --glass-cream: rgba(255, 255, 255, .76);
            --glass-cream-strong: rgba(255, 255, 255, .92);
            --glass-border: rgba(47, 43, 61, .14);
            --glass-highlight: rgba(255, 255, 255, .72);
        }
        html.dark .admin-shell {
            --gold: #7367f0;
            --gold-soft: rgba(115, 103, 240, .18);
            --glass-cream: rgba(47, 51, 73, .76);
            --glass-cream-strong: rgba(47, 51, 73, .94);
            --glass-border: rgba(225, 222, 245, .14);
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

        /*
         * Vuexy-inspired SaaS redesign layer.
         * Original implementation: no template source, assets, branding or markup copied.
         */
        :root {
            --vx-font: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            --vx-bg: #f5f6fb;
            --vx-surface: #ffffff;
            --vx-surface-soft: #f8f8fc;
            --vx-surface-glass: rgba(255, 255, 255, .82);
            --vx-sidebar: #25293c;
            --vx-sidebar-strong: #1f2333;
            --vx-sidebar-soft: rgba(255, 255, 255, .075);
            --vx-ink: #2f3349;
            --vx-muted: #6f7285;
            --vx-soft: #a5a8bd;
            --vx-line: rgba(47, 51, 73, .12);
            --vx-primary: #7367f0;
            --vx-primary-dark: #5e50ee;
            --vx-primary-soft: rgba(115, 103, 240, .14);
            --vx-info: #00bad1;
            --vx-success: #28c76f;
            --vx-warning: #ff9f43;
            --vx-danger: #ea5455;
            --vx-radius: 12px;
            --vx-radius-sm: 8px;
            --vx-shadow-sm: 0 4px 18px rgba(47, 51, 73, .08);
            --vx-shadow-md: 0 10px 30px rgba(47, 51, 73, .12);
            --vx-shadow-lg: 0 18px 54px rgba(47, 51, 73, .18);
            --vx-focus: 0 0 0 4px rgba(115, 103, 240, .18);
        }
        html.dark {
            --vx-bg: #151521;
            --vx-surface: #2f3349;
            --vx-surface-soft: #272b3f;
            --vx-surface-glass: rgba(47, 51, 73, .84);
            --vx-sidebar: #1e2130;
            --vx-sidebar-strong: #181b29;
            --vx-sidebar-soft: rgba(255, 255, 255, .07);
            --vx-ink: #e9e7fd;
            --vx-muted: #b6b4c7;
            --vx-soft: #8f8ca3;
            --vx-line: rgba(225, 222, 245, .12);
            --vx-primary-soft: rgba(115, 103, 240, .2);
            --vx-shadow-sm: 0 4px 18px rgba(0, 0, 0, .2);
            --vx-shadow-md: 0 10px 30px rgba(0, 0, 0, .28);
            --vx-shadow-lg: 0 18px 54px rgba(0, 0, 0, .36);
        }
        body,
        .is-public,
        .admin-shell {
            --ink: var(--vx-ink);
            --muted: var(--vx-muted);
            --soft: var(--vx-soft);
            --line: var(--vx-line);
            --paper: var(--vx-bg);
            --panel: var(--vx-surface);
            --panel-soft: var(--vx-surface-glass);
            --primary: var(--vx-primary);
            --accent: var(--vx-info);
            --danger: var(--vx-danger);
            --success: var(--vx-success);
            --radius: var(--vx-radius);
            --shadow-sm: var(--vx-shadow-sm);
            --shadow-md: var(--vx-shadow-md);
            --shadow-lg: var(--vx-shadow-lg);
            --focus: var(--vx-focus);
            font-family: var(--vx-font);
            color: var(--vx-ink);
        }
        body,
        .is-public {
            background:
                radial-gradient(circle at 12% 4%, rgba(115, 103, 240, .14), transparent 28%),
                radial-gradient(circle at 88% 12%, rgba(0, 186, 209, .1), transparent 26%),
                linear-gradient(180deg, var(--vx-bg) 0%, #eef0f8 100%);
            font-size: 14px;
            line-height: 1.48;
        }
        html.dark body,
        html.dark .is-public {
            background:
                radial-gradient(circle at 12% 4%, rgba(115, 103, 240, .2), transparent 30%),
                radial-gradient(circle at 88% 12%, rgba(0, 186, 209, .12), transparent 28%),
                linear-gradient(180deg, #151521 0%, #1a1d2c 100%);
        }
        body::before,
        .is-public::before,
        .admin-shell::before,
        .is-public::after,
        .admin-shell::after {
            opacity: 0 !important;
            animation: none !important;
        }
        .shell,
        .admin-shell .shell {
            width: min(1200px, calc(100% - 48px));
        }
        .topbar {
            background: rgba(255, 255, 255, .88);
            border-bottom: 1px solid var(--vx-line);
            backdrop-filter: blur(20px) saturate(1.15);
            box-shadow: 0 4px 20px rgba(47, 51, 73, .06);
        }
        html.dark .topbar {
            background: rgba(47, 51, 73, .78);
        }
        .topbar-inner {
            min-height: 72px;
        }
        .brand strong,
        .admin-brand strong {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0;
        }
        .brand span {
            color: var(--vx-muted);
            font-size: 12.5px;
        }
        .brand-logo {
            border-radius: 10px;
            border-color: var(--vx-line);
            box-shadow: var(--vx-shadow-sm);
        }
        .nav a,
        .button,
        button,
        .admin-shell .button,
        .admin-shell button,
        .admin-shell .nav a {
            min-height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 14px;
            border: 1px solid var(--vx-line);
            border-radius: 8px;
            background: var(--vx-surface);
            color: var(--vx-ink);
            font-size: 13px;
            font-weight: 600;
            line-height: 1;
            text-decoration: none;
            box-shadow: 0 2px 6px rgba(47, 51, 73, .04);
            transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease, background .16s ease, color .16s ease;
        }
        .nav a:hover,
        .button:hover,
        button:hover,
        .admin-shell .button:hover,
        .admin-shell button:hover {
            transform: translateY(-1px);
            border-color: rgba(115, 103, 240, .32);
            background: color-mix(in srgb, var(--vx-surface) 92%, var(--vx-primary) 8%);
            box-shadow: var(--vx-shadow-sm);
            color: var(--vx-primary);
        }
        .button.primary,
        button.primary,
        .admin-shell .button.primary,
        .admin-shell button.primary {
            border-color: transparent;
            background: linear-gradient(135deg, var(--vx-primary), var(--vx-primary-dark));
            color: #fff;
            box-shadow: 0 6px 18px rgba(115, 103, 240, .34);
        }
        .button::before,
        .button::after,
        button::before,
        button::after,
        .nav a::before,
        .nav a::after,
        .admin-shell .button::before,
        .admin-shell .button::after,
        .admin-shell button::before,
        .admin-shell button::after,
        .admin-shell .admin-icon-button::before,
        .admin-shell .admin-profile-summary::before {
            display: none !important;
        }
        .button.primary:hover,
        button.primary:hover,
        .admin-shell .button.primary:hover,
        .admin-shell button.primary:hover {
            background: linear-gradient(135deg, #8378f3, var(--vx-primary));
            color: #fff;
            box-shadow: 0 8px 24px rgba(115, 103, 240, .42);
        }
        .button.subtle,
        button.subtle,
        .admin-shell .button.subtle,
        .admin-shell button.subtle,
        .admin-shell .button.secondary {
            border-color: transparent;
            background: var(--vx-primary-soft);
            color: var(--vx-primary);
            box-shadow: none;
        }
        .button.ghost,
        button.ghost,
        .admin-shell .button.ghost,
        .admin-shell button.ghost {
            border-color: transparent;
            background: transparent;
            box-shadow: none;
        }
        .button.danger,
        button.danger,
        .admin-shell button.danger {
            border-color: transparent;
            background: rgba(234, 84, 85, .14);
            color: var(--vx-danger);
            box-shadow: none;
        }
        .hero,
        .admin-shell .hero {
            padding: 32px 0 20px;
        }
        .hero.compact,
        .admin-shell .hero.compact {
            padding-bottom: 12px;
        }
        .eyebrow,
        .admin-shell .eyebrow,
        .admin-kicker {
            color: var(--vx-primary);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        h1,
        .admin-shell h1 {
            margin: 7px 0 8px;
            max-width: 820px;
            color: var(--vx-ink);
            font-size: clamp(28px, 4vw, 46px);
            font-weight: 700;
            line-height: 1.08;
        }
        h2,
        .admin-shell h2 {
            margin: 0 0 14px;
            color: var(--vx-ink);
            font-size: 18px;
            font-weight: 700;
            line-height: 1.28;
        }
        h3,
        .admin-shell h3 {
            margin: 0 0 7px;
            color: var(--vx-ink);
            font-size: 14px;
            font-weight: 650;
            line-height: 1.35;
        }
        p,
        .admin-shell p {
            color: var(--vx-muted);
            font-size: 13px;
            line-height: 1.55;
        }
        .grid {
            gap: 20px;
        }
        .dashboard-grid {
            grid-template-columns: minmax(0, 1fr) 360px;
        }
        .booking-grid,
        .settings-grid {
            gap: 22px;
        }
        .panel,
        .metric,
        .booking-card,
        .staff-card,
        .day-tile,
        .settings-tabs,
        .settings-save,
        .notice,
        .empty-state,
        .modal,
        .admin-shell .panel,
        .admin-shell .metric,
        .admin-shell .booking-card,
        .admin-shell .staff-card,
        .admin-shell .day-tile,
        .admin-shell .settings-tabs,
        .admin-shell .settings-save,
        .admin-dropdown-panel {
            position: relative;
            overflow: hidden;
            border: 1px solid var(--vx-line);
            border-radius: var(--vx-radius);
            background: var(--vx-surface);
            box-shadow: var(--vx-shadow-sm);
            backdrop-filter: none;
        }
        .panel::before,
        .metric::before,
        .booking-card::before,
        .staff-card::before,
        .day-tile::before,
        .settings-tabs::before,
        .notice::before,
        .empty-state::before {
            display: none !important;
        }
        .panel,
        .admin-shell .panel {
            padding: 22px;
        }
        .panel:hover,
        .metric:hover,
        .booking-card:hover,
        .staff-card:hover,
        .day-tile:hover,
        .admin-shell .panel:hover,
        .admin-shell .metric:hover,
        .admin-shell .booking-card:hover,
        .admin-shell .staff-card:hover,
        .admin-shell .day-tile:hover {
            transform: translateY(-2px);
            border-color: rgba(115, 103, 240, .18);
            box-shadow: var(--vx-shadow-md);
        }
        .metric-row {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 22px;
        }
        .metric,
        .admin-shell .metric {
            min-height: 108px;
            padding: 18px;
        }
        .metric::after {
            content: "";
            position: absolute;
            right: -28px;
            bottom: -30px;
            width: 92px;
            height: 92px;
            border-radius: 32px;
            background: var(--vx-primary-soft);
            transform: rotate(18deg);
        }
        .metric span,
        .admin-shell .metric span {
            color: var(--vx-muted);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0;
        }
        .metric strong,
        .admin-shell .metric strong {
            position: relative;
            z-index: 1;
            display: block;
            margin-top: 9px;
            color: var(--vx-ink);
            font-size: 28px;
            font-weight: 700;
            line-height: 1;
        }
        .badge,
        .admin-shell .badge {
            min-height: 24px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 9px;
            border: 0;
            border-radius: 999px;
            background: var(--vx-primary-soft);
            color: var(--vx-primary);
            font-size: 11px;
            font-weight: 650;
            line-height: 1.1;
            box-shadow: none;
        }
        .table-list {
            gap: 7px;
        }
        .booking-card,
        .admin-shell .booking-card {
            grid-template-columns: 92px minmax(0, 1fr) auto;
            gap: 14px;
            padding: 15px;
            border-left: 4px solid var(--vx-primary);
        }
        .booking-time strong,
        .admin-shell .booking-time strong,
        .booking-card > div:first-child > strong {
            color: var(--vx-primary);
            font-size: 20px;
            font-weight: 700;
        }
        .staff-list {
            gap: 12px;
        }
        .staff-card,
        .admin-shell .staff-card {
            padding: 15px;
        }
        .quick-actions {
            gap: 10px;
        }
        .quick-actions a {
            min-height: 54px;
            background: var(--vx-surface-soft);
        }
        .empty-state {
            border-style: dashed;
            background: color-mix(in srgb, var(--vx-surface) 90%, var(--vx-primary) 10%);
        }
        .notice {
            background: rgba(0, 186, 209, .1);
            color: color-mix(in srgb, var(--vx-info) 72%, var(--vx-ink));
        }
        .success {
            border-color: rgba(40, 199, 111, .2);
            background: rgba(40, 199, 111, .12);
            color: var(--vx-success);
        }
        .errors,
        .error {
            border-color: rgba(234, 84, 85, .2);
            background: rgba(234, 84, 85, .12);
            color: var(--vx-danger);
        }
        label,
        .admin-shell label {
            color: var(--vx-ink);
            font-size: 12.5px;
            font-weight: 600;
            letter-spacing: 0;
        }
        input,
        select,
        textarea,
        .wysiwyg-editor,
        .admin-shell input,
        .admin-shell select,
        .admin-shell textarea,
        .admin-shell .wysiwyg-editor {
            min-height: 40px;
            border: 1px solid var(--vx-line);
            border-radius: 8px;
            background: var(--vx-surface);
            color: var(--vx-ink);
            padding: 9px 12px;
            font-size: 13px;
            box-shadow: none;
        }
        input:hover,
        select:hover,
        textarea:hover {
            border-color: rgba(115, 103, 240, .32);
        }
        input:focus-visible,
        select:focus-visible,
        textarea:focus-visible,
        .wysiwyg-editor:focus {
            outline: 0;
            border-color: var(--vx-primary);
            box-shadow: var(--vx-focus);
        }
        input[type="checkbox"] {
            accent-color: var(--vx-primary);
        }
        .slots {
            grid-template-columns: repeat(auto-fit, minmax(88px, 1fr));
            gap: 9px;
        }
        .slot span,
        .admin-shell .slot span {
            min-height: 38px;
            border: 1px solid var(--vx-line);
            border-radius: 8px;
            background: var(--vx-surface-soft);
            color: var(--vx-ink);
            font-size: 12.5px;
            font-weight: 650;
            box-shadow: none;
        }
        .slot:hover span,
        .admin-shell .slot:hover span {
            transform: translateY(-1px);
            border-color: rgba(115, 103, 240, .28);
            background: var(--vx-primary-soft);
            color: var(--vx-primary);
            box-shadow: none;
        }
        .slot input:checked + span,
        .admin-shell .slot input:checked + span {
            border-color: transparent;
            background: var(--vx-primary);
            color: #fff;
            box-shadow: 0 6px 18px rgba(115, 103, 240, .3);
        }
        .settings-tabs,
        .admin-shell .settings-tabs {
            gap: 6px;
            padding: 6px;
            background: var(--vx-surface);
        }
        .settings-tab,
        .admin-shell .settings-tab {
            min-height: 36px;
            border-color: transparent;
            background: transparent;
            box-shadow: none;
            font-size: 12px;
        }
        .settings-tab.active,
        .admin-shell .settings-tab.active {
            background: var(--vx-primary);
            color: #fff;
            box-shadow: 0 6px 18px rgba(115, 103, 240, .26);
        }
        .day-tile,
        .admin-shell .day-tile {
            min-height: 82px;
            padding: 12px;
        }
        .day-tile.active,
        .admin-shell .day-tile.active {
            border-color: rgba(115, 103, 240, .32);
            background: var(--vx-primary-soft);
        }
        .day-tile strong,
        .admin-shell .day-tile strong {
            color: var(--vx-ink);
            font-size: 18px;
            font-weight: 700;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
            border: 1px solid var(--vx-line);
            border-radius: var(--vx-radius);
            background: var(--vx-surface);
            box-shadow: var(--vx-shadow-sm);
        }
        th,
        td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--vx-line);
            color: var(--vx-ink);
            font-size: 13px;
            text-align: left;
        }
        th {
            color: var(--vx-muted);
            background: var(--vx-surface-soft);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        tr:last-child td {
            border-bottom: 0;
        }
        tbody tr:hover td {
            background: color-mix(in srgb, var(--vx-surface) 92%, var(--vx-primary) 8%);
        }
        .modal-backdrop {
            background: rgba(21, 21, 33, .5);
            backdrop-filter: blur(10px);
        }
        .modal {
            background: var(--vx-surface);
            border-color: var(--vx-line);
            box-shadow: var(--vx-shadow-lg);
        }
        .site-footer {
            border-top: 1px solid var(--vx-line);
            background: rgba(255, 255, 255, .78);
            backdrop-filter: blur(18px);
        }
        html.dark .site-footer {
            background: rgba(47, 51, 73, .72);
        }

        .admin-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 282px minmax(0, 1fr);
            background: var(--vx-bg);
            color: var(--vx-ink);
            font-size: 14px;
            line-height: 1.48;
        }
        .admin-shell .admin-sidebar {
            position: sticky;
            top: 0;
            z-index: 25;
            width: 282px;
            height: 100vh;
            padding: 16px 14px;
            border-right: 0;
            background:
                linear-gradient(180deg, rgba(255,255,255,.035), transparent 28%),
                linear-gradient(180deg, var(--vx-sidebar), var(--vx-sidebar-strong));
            color: #d9d8ef;
            box-shadow: 8px 0 24px rgba(47, 51, 73, .14);
        }
        .admin-brand {
            min-height: 58px;
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr);
            gap: 11px;
            align-items: center;
            padding: 8px;
            border: 0;
            border-radius: 10px;
            background: rgba(255,255,255,.06);
            color: #fff;
            box-shadow: inset 0 1px rgba(255,255,255,.08);
        }
        .admin-brand img,
        .admin-brand span {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--vx-primary);
            color: #fff;
            box-shadow: 0 8px 18px rgba(0,0,0,.16);
        }
        .admin-shell .admin-nav {
            display: grid;
            gap: 5px;
            margin-top: 16px;
        }
        .admin-nav-section {
            border: 0;
            border-radius: 10px;
            background: transparent;
            overflow: visible;
        }
        .admin-nav-section summary {
            min-height: 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 7px 10px;
            border: 1px solid transparent;
            border-radius: 8px;
            color: rgba(231, 230, 250, .72);
            font-size: 13px;
            font-weight: 600;
            list-style: none;
            cursor: pointer;
        }
        .admin-nav-section summary::-webkit-details-marker {
            display: none;
        }
        .admin-nav-section summary:hover {
            color: #fff;
            background: var(--vx-sidebar-soft);
        }
        .admin-nav-section[open] summary {
            color: #fff;
            background: rgba(115, 103, 240, .18);
            border-color: rgba(115, 103, 240, .2);
        }
        .admin-nav-title {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .admin-nav-icon {
            width: 28px;
            height: 28px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: currentColor;
        }
        .admin-nav-icon svg,
        .admin-icon-button svg,
        .button svg,
        .admin-search svg,
        .admin-nav-chevron {
            width: 17px;
            height: 17px;
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
            opacity: .62;
            transition: transform .18s ease;
        }
        .admin-nav-section[open] .admin-nav-chevron {
            transform: rotate(180deg);
        }
        .admin-nav-items {
            display: grid;
            gap: 3px;
            padding: 4px 6px 8px 47px;
        }
        .admin-shell .admin-nav a,
        .admin-nav-disabled {
            min-height: 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 6px 9px;
            border: 0;
            border-radius: 8px;
            color: rgba(231, 230, 250, .6);
            font-size: 12.5px;
            font-weight: 500;
            text-decoration: none;
        }
        .admin-shell .admin-nav a:hover {
            color: #fff;
            background: rgba(255,255,255,.06);
            transform: translateX(2px);
        }
        .admin-shell .admin-nav a.active {
            color: #fff;
            background: linear-gradient(135deg, var(--vx-primary), var(--vx-primary-dark));
            box-shadow: 0 8px 20px rgba(115, 103, 240, .32);
        }
        .admin-nav-disabled {
            opacity: .58;
            cursor: not-allowed;
        }
        .admin-nav-disabled small {
            color: rgba(231, 230, 250, .45);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .admin-shell .admin-main {
            min-width: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .admin-topbar {
            position: sticky;
            top: 0;
            z-index: 30;
            min-height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 14px 28px;
            border-bottom: 1px solid var(--vx-line);
            background: rgba(245, 246, 251, .82);
            backdrop-filter: blur(20px) saturate(1.1);
            box-shadow: 0 2px 18px rgba(47, 51, 73, .06);
            overflow: visible;
        }
        html.dark .admin-topbar {
            background: rgba(21, 21, 33, .78);
        }
        .admin-topbar-left,
        .admin-topbar-actions,
        .admin-profile-summary {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .admin-topbar-left strong {
            display: block;
            color: var(--vx-ink);
            font-size: 15px;
            font-weight: 700;
            line-height: 1.2;
        }
        .admin-search {
            width: min(320px, 28vw);
            min-height: 38px;
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 0 12px;
            border: 1px solid var(--vx-line);
            border-radius: 8px;
            background: var(--vx-surface);
            color: var(--vx-muted);
            box-shadow: var(--vx-shadow-sm);
        }
        .admin-search input {
            min-height: 0;
            height: 36px;
            border: 0;
            padding: 0;
            background: transparent;
            color: var(--vx-muted);
            font-size: 13px;
            box-shadow: none;
        }
        .admin-search input:disabled {
            opacity: 1;
            cursor: default;
        }
        .admin-search:focus-within {
            box-shadow: var(--vx-focus);
            border-color: var(--vx-primary);
        }
        .admin-icon-button {
            width: 38px;
            min-width: 38px;
            min-height: 38px;
            padding: 0;
            border-radius: 8px;
            background: var(--vx-surface);
            color: var(--vx-muted);
            box-shadow: var(--vx-shadow-sm);
        }
        .admin-icon-button:hover {
            color: var(--vx-primary);
        }
        .admin-menu-button {
            display: none;
        }
        .admin-dropdown {
            position: relative;
            flex: 0 0 auto;
        }
        .admin-dropdown summary {
            list-style: none;
            cursor: pointer;
        }
        .admin-dropdown summary::-webkit-details-marker {
            display: none;
        }
        .admin-profile-summary {
            min-height: 38px;
            padding: 4px 10px 4px 4px;
            border: 1px solid var(--vx-line);
            border-radius: 9px;
            background: var(--vx-surface);
            color: var(--vx-ink);
            box-shadow: var(--vx-shadow-sm);
        }
        .admin-profile-summary span {
            width: 30px;
            height: 30px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--vx-primary), var(--vx-primary-dark));
            color: #fff;
            font-size: 12px;
            font-weight: 700;
        }
        .admin-profile-summary strong {
            color: var(--vx-ink);
            font-size: 12.5px;
            font-weight: 650;
        }
        .admin-topbar .admin-dropdown-panel {
            position: absolute;
            top: calc(100% + 12px);
            right: 0;
            z-index: 80;
            width: min(320px, calc(100vw - 28px));
            display: grid;
            gap: 10px;
            padding: 14px;
            transform-origin: top right;
            animation: dropdownFloatIn .16s ease both;
        }
        .admin-topbar .admin-dropdown-panel.profile {
            width: min(340px, calc(100vw - 28px));
        }
        .admin-dropdown-panel a {
            color: var(--vx-primary);
            font-weight: 650;
            text-decoration: none;
        }
        .admin-dropdown-panel .logout-form button {
            width: 100%;
        }
        .admin-content {
            flex: 1;
            padding-bottom: 28px;
        }
        .admin-shell .site-footer {
            border-top-color: var(--vx-line);
            background: rgba(255,255,255,.58);
        }
        html.dark .admin-shell .site-footer {
            background: rgba(47, 51, 73, .42);
        }
        .editor-toolbar {
            background: var(--vx-surface-soft);
        }

        @media (max-width: 1180px) {
            .admin-search {
                width: min(260px, 24vw);
            }
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
                width: min(300px, calc(100vw - 28px));
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
                background: rgba(21, 21, 33, .45);
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
        @media (max-width: 900px) {
            .dashboard-grid,
            .booking-grid,
            .settings-grid,
            .metric-row {
                grid-template-columns: 1fr;
            }
            .admin-search {
                display: none;
            }
        }
        @media (max-width: 780px) {
            .shell,
            .admin-shell .shell {
                width: min(100% - 28px, 1200px);
            }
            .topbar-inner {
                min-height: auto;
                align-items: flex-start;
                flex-direction: column;
                padding: 14px 0;
            }
            .nav {
                width: 100%;
                justify-content: flex-start;
                overflow-x: auto;
                flex-wrap: nowrap;
                padding-bottom: 4px;
            }
            .nav a,
            .nav form,
            .nav button {
                flex: 0 0 auto;
                white-space: nowrap;
            }
            .admin-topbar {
                align-items: flex-start;
                flex-direction: column;
                gap: 12px;
                padding: 12px 14px;
            }
            .admin-topbar-actions {
                width: 100%;
                justify-content: flex-start;
                overflow-x: auto;
                padding-bottom: 4px;
            }
            .admin-profile-summary strong {
                display: none;
            }
            .settings-tabs,
            .form-grid,
            .week-strip,
            .staff-card,
            .booking-card {
                grid-template-columns: 1fr;
            }
            .actions,
            .modal-actions {
                align-items: stretch;
                flex-direction: column;
            }
            .actions > *,
            .modal-actions > * {
                width: 100%;
            }
            h1,
            .admin-shell h1 {
                font-size: 34px;
            }
        }

        /*
         * Closer Vuexy-style admin polish.
         * This reproduces the visual behaviour with original CSS only.
         */
        .admin-shell {
            --vx-bg: #f8f7fa;
            --vx-surface: #fff;
            --vx-surface-soft: #f6f6f9;
            --vx-ink: #444050;
            --vx-muted: #6d6777;
            --vx-line: #dbdade;
            --vx-sidebar: #2f3349;
            --vx-sidebar-deep: #25293c;
            --vx-sidebar-text: #cfcde4;
            --vx-sidebar-muted: #a4a1bd;
            --vx-primary: #7367f0;
            --vx-primary-hover: #685dd8;
            --vx-menu-hover: rgba(255, 255, 255, .08);
            --vx-radius: 6px;
            --vx-shadow-navbar: 0 .125rem .375rem rgba(47, 43, 61, .14);
            --vx-shadow-dropdown: 0 .25rem 1.125rem rgba(47, 43, 61, .16);
            grid-template-columns: 260px minmax(0, 1fr);
            background: var(--vx-bg);
        }
        html.dark .admin-shell {
            --vx-bg: #25293c;
            --vx-surface: #2f3349;
            --vx-surface-soft: #34384f;
            --vx-ink: #d5d1ea;
            --vx-muted: #b6b1cb;
            --vx-line: rgba(225, 222, 245, .16);
            --vx-sidebar: #2f3349;
            --vx-sidebar-deep: #25293c;
        }
        .admin-shell .admin-sidebar {
            width: 260px;
            padding: 12px 14px;
            background: linear-gradient(180deg, var(--vx-sidebar) 0%, var(--vx-sidebar-deep) 100%);
            color: var(--vx-sidebar-text);
            box-shadow: none;
        }
        .admin-brand {
            min-height: 58px;
            padding: 6px 8px 12px;
            border-radius: 0;
            background: transparent;
            color: #fff;
            box-shadow: none;
        }
        .admin-brand img,
        .admin-brand span {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: var(--vx-primary);
            box-shadow: none;
        }
        .admin-brand strong {
            color: #fff;
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: -.01em;
        }
        .admin-shell .admin-nav {
            gap: 2px;
            margin-top: 4px;
        }
        .admin-nav-section summary {
            min-height: 38px;
            padding: 8px 12px;
            border: 0;
            border-radius: 6px;
            color: var(--vx-sidebar-text);
            font-size: .9375rem;
            font-weight: 400;
        }
        .admin-nav-section summary:hover {
            color: #fff;
            background: var(--vx-menu-hover);
        }
        .admin-nav-section[open] summary {
            color: #fff;
            background: transparent;
            box-shadow: none;
        }
        .admin-nav-title {
            gap: 10px;
        }
        .admin-nav-icon {
            width: 22px;
            height: 22px;
            border-radius: 0;
        }
        .admin-nav-icon svg,
        .admin-icon-button svg,
        .button svg,
        .admin-search svg {
            width: 20px;
            height: 20px;
            stroke-width: 1.65;
        }
        .admin-nav-chevron {
            width: 16px;
            height: 16px;
            opacity: .7;
        }
        .admin-nav-items {
            gap: 2px;
            padding: 2px 0 6px 34px;
        }
        .admin-shell .admin-nav a,
        .admin-nav-disabled {
            min-height: 34px;
            padding: 7px 12px;
            border-radius: 6px;
            color: var(--vx-sidebar-muted);
            font-size: .875rem;
            font-weight: 400;
        }
        .admin-shell .admin-nav a::before,
        .admin-nav-disabled::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 999px;
            margin-right: 2px;
            background: currentColor;
            opacity: .52;
        }
        .admin-shell .admin-nav a {
            justify-content: flex-start;
        }
        .admin-shell .admin-nav a:hover {
            color: #fff;
            background: var(--vx-menu-hover);
            transform: translateX(3px);
        }
        .admin-shell .admin-nav a.active {
            color: #fff;
            background: linear-gradient(270deg, var(--vx-primary) 0%, #9b8cff 100%);
            box-shadow: 0 .125rem .375rem rgba(115, 103, 240, .35);
            transform: none;
        }
        .admin-shell .admin-nav a.active::before {
            opacity: .9;
            background: #fff;
        }
        .admin-nav-disabled {
            justify-content: flex-start;
            opacity: .58;
        }
        .admin-nav-disabled small {
            margin-left: auto;
            color: rgba(207, 205, 228, .5);
        }
        .admin-topbar {
            min-height: 64px;
            margin: 12px 24px 0;
            padding: 10px 18px;
            border: 0;
            border-radius: 6px;
            background: rgba(255, 255, 255, .95);
            color: var(--vx-ink);
            box-shadow: var(--vx-shadow-navbar);
            backdrop-filter: saturate(200%) blur(6px);
        }
        html.dark .admin-topbar {
            background: rgba(47, 51, 73, .95);
            box-shadow: 0 .125rem .5rem rgba(0, 0, 0, .28);
        }
        .admin-topbar-left strong {
            color: var(--vx-ink);
            font-size: .9375rem;
            font-weight: 600;
        }
        .admin-kicker {
            color: var(--vx-muted);
            font-size: .6875rem;
            font-weight: 500;
            letter-spacing: .04em;
        }
        .admin-search {
            width: min(360px, 30vw);
            min-height: 38px;
            border: 0;
            background: transparent;
            color: var(--vx-muted);
            box-shadow: none;
            padding: 0 6px;
        }
        .admin-search input {
            height: 38px;
            background: transparent;
            color: var(--vx-muted);
            font-size: .9375rem;
        }
        .admin-search:focus-within {
            box-shadow: none;
        }
        .admin-shell .button,
        .admin-shell button,
        .admin-icon-button,
        .admin-profile-summary {
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: var(--vx-muted);
            box-shadow: none;
        }
        .admin-shell .button:hover,
        .admin-shell button:hover,
        .admin-icon-button:hover,
        .admin-profile-summary:hover {
            background: rgba(47, 43, 61, .06);
            color: var(--vx-primary);
            box-shadow: none;
        }
        html.dark .admin-shell .button:hover,
        html.dark .admin-shell button:hover,
        html.dark .admin-icon-button:hover,
        html.dark .admin-profile-summary:hover {
            background: rgba(225, 222, 245, .08);
        }
        .admin-shell .button.primary,
        .admin-shell button.primary {
            border: 0;
            background: var(--vx-primary);
            color: #fff;
            box-shadow: 0 .125rem .375rem rgba(115, 103, 240, .35);
        }
        .admin-shell .button.primary:hover,
        .admin-shell button.primary:hover {
            background: var(--vx-primary-hover);
            color: #fff;
            box-shadow: 0 .25rem .75rem rgba(115, 103, 240, .35);
        }
        .admin-icon-button {
            width: 38px;
            min-width: 38px;
            min-height: 38px;
            padding: 0;
        }
        .admin-profile-summary {
            min-height: 38px;
            padding: 4px 8px 4px 4px;
            gap: 8px;
        }
        .admin-profile-summary span {
            width: 32px;
            height: 32px;
            border-radius: 999px;
            background: #7367f0;
            color: #fff;
        }
        .admin-profile-summary strong {
            color: var(--vx-ink);
            font-size: .875rem;
            font-weight: 500;
        }
        .admin-topbar .admin-dropdown-panel {
            top: calc(100% + 10px);
            min-width: 15rem;
            padding: .5rem 0;
            border: 0;
            border-radius: 6px;
            background: var(--vx-surface);
            box-shadow: var(--vx-shadow-dropdown);
            overflow: hidden;
        }
        .admin-topbar .admin-dropdown-panel.profile {
            min-width: 14rem;
        }
        .admin-dropdown-panel strong,
        .admin-dropdown-panel p,
        .admin-dropdown-panel a,
        .admin-dropdown-panel form {
            margin: 0;
            padding: .55rem 1rem;
        }
        .admin-dropdown-panel strong {
            color: var(--vx-ink);
            font-size: .9375rem;
            font-weight: 600;
        }
        .admin-dropdown-panel p {
            color: var(--vx-muted);
            font-size: .8125rem;
        }
        .admin-dropdown-panel a {
            display: flex;
            align-items: center;
            color: var(--vx-muted);
            font-size: .9375rem;
            font-weight: 400;
        }
        .admin-dropdown-panel a:hover {
            background: rgba(47, 43, 61, .06);
            color: var(--vx-primary);
        }
        .admin-dropdown-panel .logout-form {
            padding-top: .35rem;
            border-top: 1px solid var(--vx-line);
        }
        .admin-dropdown-panel .logout-form button {
            width: 100%;
            justify-content: flex-start;
            padding: .55rem 1rem;
            color: var(--vx-muted);
        }
        .admin-content {
            padding: 0 0 28px;
        }
        .admin-shell .hero {
            padding-top: 28px;
        }
        .panel,
        .metric,
        .booking-card,
        .staff-card,
        .day-tile,
        .settings-tabs,
        .settings-save,
        .admin-shell .panel,
        .admin-shell .metric,
        .admin-shell .booking-card,
        .admin-shell .staff-card,
        .admin-shell .day-tile,
        .admin-shell .settings-tabs,
        .admin-shell .settings-save {
            border-color: var(--vx-line);
            border-radius: 6px;
            box-shadow: 0 .25rem 1.125rem rgba(47, 43, 61, .08);
        }
        .panel:hover,
        .metric:hover,
        .booking-card:hover,
        .staff-card:hover,
        .day-tile:hover,
        .admin-shell .panel:hover,
        .admin-shell .metric:hover,
        .admin-shell .booking-card:hover,
        .admin-shell .staff-card:hover,
        .admin-shell .day-tile:hover {
            transform: translateY(-1px);
            box-shadow: 0 .5rem 1.5rem rgba(47, 43, 61, .12);
        }
        @media (max-width: 1080px) {
            .admin-shell .admin-sidebar {
                width: min(260px, calc(100vw - 28px));
            }
        }
        @media (max-width: 780px) {
            .admin-topbar {
                margin: 12px 14px 0;
            }
        }

        /* Final no-warm-theme enforcement: remove legacy cream/gold remnants. */
        .admin-shell,
        .is-public {
            --gold: var(--vx-primary);
            --gold-soft: var(--vx-primary-soft);
            --glass-cream: var(--vx-surface);
            --glass-cream-strong: var(--vx-surface);
            --glass-border: var(--vx-line);
            --glass-highlight: rgba(255, 255, 255, .08);
        }
        .admin-shell .admin-sidebar,
        .admin-shell .admin-sidebar *,
        .admin-shell .admin-sidebar *::before,
        .admin-shell .admin-sidebar *::after {
            box-shadow: none;
        }
        .admin-shell .admin-nav-section,
        .admin-shell .admin-nav-section[open],
        .admin-shell .admin-nav-section summary,
        .admin-shell .admin-nav-section[open] summary,
        .admin-shell .admin-nav-icon {
            border-color: transparent;
            background: transparent;
        }
        .admin-shell .admin-nav-section summary {
            color: var(--vx-sidebar-text);
        }
        .admin-shell .admin-nav-section summary:hover {
            color: #fff;
            background: rgba(255, 255, 255, .08);
        }
        .admin-shell .admin-nav-section[open] summary {
            color: #fff;
        }
        .admin-shell .admin-nav a::after {
            display: none !important;
            content: none !important;
        }
        .admin-shell .admin-nav a.active,
        .admin-shell .admin-nav a.active:hover {
            color: #fff;
            background: linear-gradient(72deg, #7367f0 22%, #9b8cff 100%);
            box-shadow: 0 .125rem .375rem rgba(115, 103, 240, .35);
        }
        .admin-shell .admin-nav a:not(.active):hover {
            color: #fff;
            background: rgba(255, 255, 255, .08);
            box-shadow: none;
        }
        .admin-shell .admin-brand,
        .admin-shell .admin-brand:hover {
            background: transparent;
            box-shadow: none;
        }
        .admin-shell .admin-brand img,
        .admin-shell .admin-brand span {
            background: #7367f0;
        }
        .admin-shell .admin-topbar,
        .admin-shell .admin-dropdown-panel,
        .admin-shell .panel,
        .admin-shell .metric,
        .admin-shell .booking-card,
        .admin-shell .staff-card,
        .admin-shell .day-tile,
        .admin-shell .settings-tabs,
        .admin-shell .settings-save,
        .is-public .panel,
        .is-public .notice,
        .is-public .empty-state,
        .is-public .modal {
            border-color: var(--vx-line);
            background-color: var(--vx-surface);
        }
        .admin-shell .button,
        .admin-shell button,
        .admin-shell .admin-icon-button,
        .admin-shell .admin-profile-summary,
        .is-public .button,
        .is-public button,
        .is-public .nav a {
            border-color: transparent;
            background-image: none;
        }
        .admin-shell .button.primary,
        .admin-shell button.primary,
        .is-public .button.primary,
        .is-public button.primary {
            border-color: transparent;
            background: #7367f0;
            color: #fff;
            box-shadow: 0 .125rem .375rem rgba(115, 103, 240, .35);
        }
        .admin-shell .button.primary:hover,
        .admin-shell button.primary:hover,
        .is-public .button.primary:hover,
        .is-public button.primary:hover {
            background: #685dd8;
            color: #fff;
            box-shadow: 0 .25rem .75rem rgba(115, 103, 240, .35);
        }
        .admin-shell .button.subtle,
        .admin-shell button.subtle,
        .admin-shell .button.secondary,
        .is-public .button.subtle,
        .is-public button.subtle {
            background: rgba(115, 103, 240, .12);
            color: #7367f0;
            box-shadow: none;
        }
        .admin-shell .slot input:checked + span,
        .is-public .slot input:checked + span,
        .settings-tab.active {
            border-color: transparent;
            background: #7367f0;
            color: #fff;
            box-shadow: 0 .125rem .375rem rgba(115, 103, 240, .28);
        }
        .admin-shell .slot:hover span,
        .is-public .slot:hover span,
        .admin-shell .settings-tab:hover,
        .is-public .nav a:hover {
            border-color: transparent;
            background: rgba(115, 103, 240, .12);
            color: #7367f0;
            box-shadow: none;
        }
        .admin-shell .badge,
        .is-public .badge {
            border-color: transparent;
            background: rgba(115, 103, 240, .12);
            color: #7367f0;
            box-shadow: none;
        }
        .admin-shell .panel::before,
        .admin-shell .metric::before,
        .admin-shell .booking-card::before,
        .admin-shell .staff-card::before,
        .admin-shell .day-tile::before,
        .admin-shell .settings-tabs::before,
        .admin-shell .button::before,
        .admin-shell button::before,
        .admin-shell .button::after,
        .admin-shell button::after,
        .is-public .panel::before,
        .is-public .button::before,
        .is-public button::before,
        .is-public .button::after,
        .is-public button::after {
            display: none !important;
            content: none !important;
        }
        .is-public {
            background:
                radial-gradient(circle at 12% 4%, rgba(115, 103, 240, .14), transparent 28%),
                radial-gradient(circle at 88% 12%, rgba(0, 186, 209, .1), transparent 26%),
                linear-gradient(180deg, #f8f7fa 0%, #f1f0f6 100%);
        }
        html.dark .is-public {
            background:
                radial-gradient(circle at 12% 4%, rgba(115, 103, 240, .2), transparent 30%),
                radial-gradient(circle at 88% 12%, rgba(0, 186, 209, .12), transparent 28%),
                linear-gradient(180deg, #25293c 0%, #2f3349 100%);
        }

        /* Topbar-only Vuexy-style refinement. */
        .admin-shell .admin-topbar {
            min-height: 64px;
            margin: 12px 24px 0;
            padding: 0 1rem;
            border: 0;
            border-radius: .375rem;
            background: rgba(255, 255, 255, .95) !important;
            color: #444050;
            box-shadow: 0 .125rem .375rem rgba(47, 43, 61, .14);
            backdrop-filter: saturate(200%) blur(6px);
        }
        html.dark .admin-shell .admin-topbar {
            background: rgba(47, 51, 73, .95) !important;
            color: #d5d1ea;
            box-shadow: 0 .125rem .5rem rgba(0, 0, 0, .28);
        }
        .admin-shell .admin-topbar-left {
            flex: 1 1 auto;
            min-width: 0;
            gap: .75rem;
        }
        .admin-shell .admin-topbar-actions {
            flex: 0 0 auto;
            gap: .25rem;
        }
        .admin-shell .admin-venue-title {
            min-width: 0;
            padding-inline-start: .25rem;
            opacity: .82;
        }
        .admin-shell .admin-venue-title strong {
            display: block;
            max-width: 190px;
            overflow: hidden;
            color: #444050;
            font-size: .8125rem;
            font-weight: 500;
            line-height: 1.2;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        html.dark .admin-shell .admin-venue-title strong {
            color: #d5d1ea;
        }
        .admin-shell .admin-kicker {
            color: #a8a3b9;
            font-size: .625rem;
            font-weight: 500;
            letter-spacing: .04em;
        }
        .admin-shell .admin-search {
            width: min(320px, 32vw);
            min-height: 64px;
            display: flex;
            align-items: center;
            gap: .625rem;
            padding: 0;
            border: 0;
            border-radius: 0;
            background: transparent !important;
            color: #6d6777;
            box-shadow: none;
        }
        html.dark .admin-shell .admin-search {
            color: #b6b1cb;
        }
        .admin-shell .admin-search svg {
            width: 1.25rem;
            height: 1.25rem;
            stroke-width: 1.75;
        }
        .admin-shell .admin-search input {
            height: 64px;
            border: 0;
            background: transparent !important;
            color: currentColor;
            padding: 0;
            font-size: .9375rem;
            font-weight: 400;
            box-shadow: none;
        }
        .admin-shell .admin-search input::placeholder {
            color: currentColor;
            opacity: .9;
        }
        .admin-shell .admin-search:focus-within {
            box-shadow: none;
        }
        .admin-shell .admin-icon-button,
        .admin-shell .admin-profile-summary,
        .admin-shell .admin-topbar-actions > .button {
            min-width: 2.375rem;
            min-height: 2.375rem;
            height: 2.375rem;
            border: 0;
            border-radius: 50%;
            background: transparent !important;
            color: #6d6777;
            box-shadow: none;
        }
        html.dark .admin-shell .admin-icon-button,
        html.dark .admin-shell .admin-profile-summary,
        html.dark .admin-shell .admin-topbar-actions > .button {
            color: #b6b1cb;
        }
        .admin-shell .admin-icon-button:hover,
        .admin-shell .admin-profile-summary:hover,
        .admin-shell .admin-topbar-actions > .button:hover {
            background: rgba(47, 43, 61, .06) !important;
            color: #7367f0;
            transform: none;
            box-shadow: none;
        }
        html.dark .admin-shell .admin-icon-button:hover,
        html.dark .admin-shell .admin-profile-summary:hover,
        html.dark .admin-shell .admin-topbar-actions > .button:hover {
            background: rgba(225, 222, 245, .08) !important;
        }
        .admin-shell .admin-topbar-actions > .button.primary {
            width: auto;
            min-width: 0;
            border-radius: .375rem;
            padding: 0 .875rem;
            background: #7367f0 !important;
            color: #fff;
            box-shadow: 0 .125rem .375rem rgba(115, 103, 240, .35);
        }
        .admin-shell .admin-topbar-actions > .button.primary:hover {
            background: #685dd8 !important;
            color: #fff;
            box-shadow: 0 .25rem .75rem rgba(115, 103, 240, .35);
        }
        .admin-shell .admin-icon-button svg,
        .admin-shell .admin-topbar-actions > .button svg {
            width: 1.25rem;
            height: 1.25rem;
            stroke-width: 1.75;
        }
        .admin-shell .admin-profile-summary {
            width: auto;
            padding: .1875rem;
            gap: .5rem;
            border-radius: 999px;
        }
        .admin-shell .admin-profile-summary span {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background: #7367f0;
            color: #fff;
            font-size: .8125rem;
            font-weight: 600;
        }
        .admin-shell .admin-profile-summary img,
        .admin-shell .admin-profile-card img,
        .admin-shell .profile-preview img {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            object-fit: cover;
        }
        .admin-shell .admin-profile-summary strong {
            display: none;
        }
        .admin-shell .admin-topbar .admin-dropdown-panel {
            top: calc(100% + .625rem);
            right: 0;
            min-width: 15rem;
            padding: .5rem 0;
            border: 0;
            border-radius: .375rem;
            background: #fff !important;
            color: #444050;
            box-shadow: 0 .25rem 1.125rem rgba(47, 43, 61, .16);
        }
        html.dark .admin-shell .admin-topbar .admin-dropdown-panel {
            background: #2f3349 !important;
            color: #d5d1ea;
            box-shadow: 0 .25rem 1.125rem rgba(0, 0, 0, .34);
        }
        .admin-shell .admin-dropdown-panel strong,
        .admin-shell .admin-dropdown-panel p,
        .admin-shell .admin-dropdown-panel a,
        .admin-shell .admin-dropdown-panel form {
            padding: .625rem 1rem;
        }
        .admin-shell .admin-dropdown-panel strong {
            color: #444050;
            font-size: .9375rem;
            font-weight: 600;
        }
        html.dark .admin-shell .admin-dropdown-panel strong {
            color: #d5d1ea;
        }
        .admin-shell .admin-dropdown-panel p,
        .admin-shell .admin-dropdown-panel a,
        .admin-shell .admin-dropdown-panel .logout-form button {
            color: #6d6777;
            font-size: .9375rem;
            font-weight: 400;
        }
        html.dark .admin-shell .admin-dropdown-panel p,
        html.dark .admin-shell .admin-dropdown-panel a,
        html.dark .admin-shell .admin-dropdown-panel .logout-form button {
            color: #b6b1cb;
        }
        .admin-shell .admin-dropdown-panel a:hover,
        .admin-shell .admin-dropdown-panel .logout-form button:hover {
            background: rgba(47, 43, 61, .06) !important;
            color: #7367f0;
            transform: none;
            box-shadow: none;
        }
        html.dark .admin-shell .admin-dropdown-panel a:hover,
        html.dark .admin-shell .admin-dropdown-panel .logout-form button:hover {
            background: rgba(225, 222, 245, .08) !important;
        }
        .admin-shell .admin-dropdown-panel .logout-form {
            border-top: 1px solid #dbdade;
            margin-top: .25rem;
            padding-top: .35rem;
        }
        html.dark .admin-shell .admin-dropdown-panel .logout-form {
            border-top-color: rgba(225, 222, 245, .16);
        }
        .admin-shell [data-theme-toggle] {
            position: relative;
        }
        .admin-shell [data-theme-toggle] .theme-icon {
            width: 1.75rem;
            height: 1.75rem;
            stroke-width: 1.9;
        }
        .admin-shell [data-theme-toggle] .theme-icon-sun {
            display: none;
        }
        html.dark .admin-shell [data-theme-toggle] .theme-icon-moon {
            display: none;
        }
        html.dark .admin-shell [data-theme-toggle] .theme-icon-sun {
            display: block;
        }
        html.dark .admin-shell {
            --vx-bg: #25293c;
            --vx-surface: #2f3349;
            --vx-surface-soft: #34384f;
            --vx-ink: #d5d1ea;
            --vx-muted: #b6b1cb;
            --vx-line: rgba(225, 222, 245, .16);
            --vx-sidebar: #2f3349;
            --vx-sidebar-deep: #25293c;
            --vx-primary: #7367f0;
            --vx-primary-hover: #685dd8;
            background: #25293c !important;
            color: #d5d1ea;
        }
        html.dark .admin-shell,
        html.dark .admin-main,
        html.dark .admin-content {
            background: #25293c !important;
        }
        html.dark .admin-shell .admin-sidebar {
            background: linear-gradient(180deg, #2f3349 0%, #25293c 100%) !important;
        }
        html.dark .admin-shell .admin-topbar,
        html.dark .admin-shell .admin-dropdown-panel,
        html.dark .admin-shell .panel,
        html.dark .admin-shell .metric,
        html.dark .admin-shell .booking-card,
        html.dark .admin-shell .staff-card,
        html.dark .admin-shell .day-tile,
        html.dark .admin-shell .settings-tabs,
        html.dark .admin-shell .settings-save,
        html.dark .admin-shell .modal,
        html.dark .admin-shell input,
        html.dark .admin-shell select,
        html.dark .admin-shell textarea,
        html.dark .admin-shell .wysiwyg-editor,
        html.dark .admin-shell .editor-toolbar {
            border-color: rgba(225, 222, 245, .16) !important;
            background: #2f3349 !important;
            color: #d5d1ea !important;
        }
        html.dark .admin-shell .panel:hover,
        html.dark .admin-shell .metric:hover,
        html.dark .admin-shell .booking-card:hover,
        html.dark .admin-shell .staff-card:hover,
        html.dark .admin-shell .day-tile:hover {
            border-color: rgba(115, 103, 240, .32) !important;
            background: #34384f !important;
        }
        html.dark .admin-shell p,
        html.dark .admin-shell .muted,
        html.dark .admin-shell label,
        html.dark .admin-shell .metric span,
        html.dark .admin-shell .admin-search,
        html.dark .admin-shell .admin-search input,
        html.dark .admin-shell .admin-icon-button,
        html.dark .admin-shell .admin-profile-summary,
        html.dark .admin-shell .admin-dropdown-panel p,
        html.dark .admin-shell .admin-dropdown-panel a,
        html.dark .admin-shell .admin-dropdown-panel .logout-form button {
            color: #b6b1cb !important;
        }
        html.dark .admin-shell h1,
        html.dark .admin-shell h2,
        html.dark .admin-shell h3,
        html.dark .admin-shell .metric strong,
        html.dark .admin-shell .admin-venue-title strong,
        html.dark .admin-shell .admin-dropdown-panel strong {
            color: #d5d1ea !important;
        }
        html.dark .admin-shell .button.primary,
        html.dark .admin-shell button.primary,
        html.dark .admin-shell .admin-topbar-actions > .button.primary,
        html.dark .admin-shell .settings-tab.active,
        html.dark .admin-shell .slot input:checked + span,
        html.dark .admin-shell .admin-nav a.active {
            border-color: transparent !important;
            background: #7367f0 !important;
            color: #fff !important;
            box-shadow: 0 .125rem .375rem rgba(115, 103, 240, .35) !important;
        }
        html.dark .admin-shell .button.subtle,
        html.dark .admin-shell button.subtle,
        html.dark .admin-shell .badge,
        html.dark .admin-shell .slot:hover span,
        html.dark .admin-shell .settings-tab:hover,
        html.dark .admin-shell .admin-icon-button:hover,
        html.dark .admin-shell .admin-profile-summary:hover,
        html.dark .admin-shell .admin-dropdown-panel a:hover,
        html.dark .admin-shell .admin-dropdown-panel .logout-form button:hover {
            border-color: transparent !important;
            background: rgba(115, 103, 240, .16) !important;
            color: #7367f0 !important;
            box-shadow: none !important;
        }
        html.dark .admin-shell .notice {
            border-color: rgba(0, 186, 209, .22) !important;
            background: rgba(0, 186, 209, .12) !important;
            color: #00bad1 !important;
        }
        html.dark .admin-shell .success {
            border-color: rgba(40, 199, 111, .22) !important;
            background: rgba(40, 199, 111, .12) !important;
            color: #28c76f !important;
        }
        html.dark .admin-shell .errors,
        html.dark .admin-shell .error {
            border-color: rgba(234, 84, 85, .22) !important;
            background: rgba(234, 84, 85, .12) !important;
            color: #ea5455 !important;
        }
        html.dark .admin-shell .site-footer {
            border-top-color: rgba(225, 222, 245, .16) !important;
            background: #25293c !important;
        }
        .admin-shell .admin-venue-title {
            display: none !important;
        }
        .admin-shell .admin-profile-card {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .75rem 1rem;
        }
        .admin-shell .admin-profile-card span,
        .admin-shell .profile-preview span {
            width: 2.375rem;
            height: 2.375rem;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #7367f0;
            color: #fff;
            font-weight: 700;
        }
        .admin-shell .admin-profile-card img {
            width: 2.375rem;
            height: 2.375rem;
        }
        .admin-shell .admin-profile-card strong {
            display: block;
            padding: 0;
            color: #444050;
            line-height: 1.2;
        }
        .admin-shell .admin-profile-card p {
            padding: 0;
            margin: .15rem 0 0;
            font-size: .8125rem;
        }
        .admin-shell .admin-dropdown-panel.profile > a,
        .admin-shell .admin-dropdown-panel.profile .logout-form button {
            gap: .75rem;
        }
        .admin-shell .admin-dropdown-panel.profile svg {
            width: 1.125rem;
            height: 1.125rem;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 1.75;
        }
        .admin-shell .admin-dropdown-panel.profile .dropdown-logout {
            margin: .5rem 1rem .25rem;
            width: calc(100% - 2rem);
            justify-content: center;
            border-radius: .375rem;
            background: #ea5455 !important;
            color: #fff !important;
            box-shadow: 0 .125rem .375rem rgba(234, 84, 85, .35) !important;
        }
        .admin-shell .admin-dropdown-panel.profile .dropdown-logout:hover {
            background: #d84a4b !important;
            color: #fff !important;
        }
        .admin-shell .profile-preview {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .admin-shell .profile-preview img,
        .admin-shell .profile-preview span {
            width: 4rem;
            height: 4rem;
            flex: 0 0 auto;
            font-size: 1.35rem;
        }
        html.dark .admin-shell .admin-profile-card strong {
            color: #d5d1ea !important;
        }
        html.dark .admin-shell .admin-profile-card p {
            color: #b6b1cb !important;
        }
        html.dark .admin-shell .admin-dropdown-panel.profile .dropdown-logout {
            background: #ea5455 !important;
            color: #fff !important;
        }
        html.dark .admin-shell [style*="40,31,20"],
        html.dark .admin-shell [style*="34,26,16"],
        html.dark .admin-shell [style*="fffaf1"],
        html.dark .admin-shell [style*="f7f1e8"] {
            background: #2f3349 !important;
            color: #d5d1ea !important;
        }
        @media (max-width: 900px) {
            .admin-shell .admin-search {
                display: flex;
                width: min(220px, 42vw);
            }
            .admin-shell .admin-venue-title {
                display: none;
            }
        }
        @media (max-width: 780px) {
            .admin-shell .admin-topbar {
                min-height: auto;
                margin: 12px 14px 0;
                padding: .625rem;
            }
            .admin-shell .admin-search {
                min-height: 2.375rem;
                width: 100%;
            }
            .admin-shell .admin-search input {
                height: 2.375rem;
            }
        }
        .admin-shell .admin-topbar-actions > [data-theme-toggle].admin-icon-button {
            width: 2.375rem !important;
            min-width: 2.375rem !important;
            height: 2.375rem !important;
            min-height: 2.375rem !important;
            padding: 0 !important;
        }
        .admin-shell .admin-topbar-actions > [data-theme-toggle].admin-icon-button svg.theme-icon {
            width: 1.42rem !important;
            height: 1.42rem !important;
            stroke-width: 1.75 !important;
            flex: 0 0 auto;
        }
        .admin-shell .admin-brand.resora-brand {
            display: flex !important;
            align-items: center;
            justify-content: center;
            min-height: 82px;
            padding: .85rem .75rem !important;
            border: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }
        .admin-shell .admin-brand.resora-brand img {
            display: block !important;
            width: min(100%, 210px) !important;
            height: auto !important;
            max-height: 68px;
            object-fit: contain !important;
            border-radius: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }
        .is-public .brand-logo.resora-public-logo {
            width: 116px;
            height: auto;
            max-height: 44px;
            border: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
        }
        .admin-shell .admin-nav-items a.locked {
            color: #b6b1cb;
        }
        .admin-shell .admin-nav-items a.locked small {
            margin-left: auto;
            border-radius: 999px;
            padding: .1rem .4rem;
            background: rgba(115, 103, 240, .14);
            color: #7367f0;
            font-size: .65rem;
            font-weight: 700;
        }
        .admin-shell .billing-suite,
        .admin-shell .locked-feature-wrap {
            display: grid;
            gap: 1rem;
            padding-bottom: 48px;
        }
        .admin-shell .billing-status-grid,
        .admin-shell .billing-plan-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }
        .admin-shell .billing-status-card,
        .admin-shell .billing-plan-card {
            position: relative;
            overflow: hidden;
            display: grid;
            gap: .85rem;
            align-content: start;
        }
        .admin-shell .billing-status-card::before,
        .admin-shell .billing-plan-card::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at 88% 12%, var(--billing-glow, rgba(115, 103, 240, .14)), transparent 32%),
                linear-gradient(135deg, var(--billing-wash, rgba(115, 103, 240, .06)), transparent 54%);
            opacity: .95;
        }
        .admin-shell .billing-status-card::after,
        .admin-shell .billing-plan-card::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--billing-accent, #7367f0);
        }
        .admin-shell .billing-status-card > *,
        .admin-shell .billing-plan-card > * {
            position: relative;
            z-index: 1;
        }
        .admin-shell .billing-status-card > span {
            color: #6d6777;
            font-size: .8125rem;
            font-weight: 600;
        }
        html.dark .admin-shell .billing-status-card > span {
            color: #b6b1cb;
        }
        .admin-shell .billing-status-card > strong {
            color: #444050;
            font-size: 1.45rem;
            font-weight: 650;
        }
        html.dark .admin-shell .billing-status-card > strong {
            color: #d5d1ea;
        }
        .admin-shell .billing-status-card.success {
            --billing-accent: #28c76f;
            --billing-glow: rgba(40, 199, 111, .16);
            --billing-wash: rgba(40, 199, 111, .08);
            border-color: rgba(40, 199, 111, .28);
        }
        .admin-shell .billing-status-card.info {
            --billing-accent: #00bad1;
            --billing-glow: rgba(0, 186, 209, .16);
            --billing-wash: rgba(0, 186, 209, .08);
            border-color: rgba(0, 186, 209, .28);
        }
        .admin-shell .billing-status-card.warning {
            --billing-accent: #ff9f43;
            --billing-glow: rgba(255, 159, 67, .16);
            --billing-wash: rgba(255, 159, 67, .08);
            border-color: rgba(255, 159, 67, .28);
        }
        .admin-shell .billing-status-card.danger {
            --billing-accent: #ea5455;
            --billing-glow: rgba(234, 84, 85, .16);
            --billing-wash: rgba(234, 84, 85, .08);
            border-color: rgba(234, 84, 85, .28);
        }
        .admin-shell .billing-plan-card.plan-starter {
            --billing-accent: #00bad1;
            --billing-glow: rgba(0, 186, 209, .16);
            --billing-wash: rgba(0, 186, 209, .08);
        }
        .admin-shell .billing-plan-card.plan-professional {
            --billing-accent: #7367f0;
            --billing-glow: rgba(115, 103, 240, .18);
            --billing-wash: rgba(115, 103, 240, .09);
        }
        .admin-shell .billing-plan-card.plan-premium {
            --billing-accent: #28c76f;
            --billing-glow: rgba(40, 199, 111, .16);
            --billing-wash: rgba(40, 199, 111, .08);
        }
        .admin-shell .billing-plan-card.recommended {
            border-color: rgba(115, 103, 240, .48);
            box-shadow: 0 .75rem 2rem rgba(115, 103, 240, .18);
        }
        .admin-shell .billing-price {
            color: #444050;
            font-size: 2.2rem;
            font-weight: 700;
            line-height: 1;
        }
        html.dark .admin-shell .billing-price {
            color: #d5d1ea;
        }
        .admin-shell .billing-price span {
            color: #6d6777;
            font-size: .875rem;
            font-weight: 500;
        }
        .admin-shell .billing-plan-card ul {
            display: grid;
            gap: .5rem;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .admin-shell .billing-plan-card li {
            color: #6d6777;
            font-size: .875rem;
        }
        html.dark .admin-shell .billing-plan-card li {
            color: #b6b1cb;
        }
        .admin-shell .billing-plan-card li::before {
            content: "";
            display: inline-block;
            width: .42rem;
            height: .42rem;
            margin-right: .5rem;
            border-radius: 999px;
            background: var(--billing-accent, #7367f0);
            vertical-align: middle;
        }
        .admin-shell .locked-feature-wrap {
            min-height: calc(100vh - 180px);
            align-items: center;
        }
        .admin-shell .locked-feature-card {
            max-width: 680px;
            margin: 0 auto;
            text-align: center;
        }
        .admin-shell .locked-feature-card h1 {
            max-width: none;
        }
        @media (max-width: 1080px) {
            .admin-shell .billing-status-grid,
            .admin-shell .billing-plan-grid {
                grid-template-columns: 1fr;
            }
        }
        .admin-shell .dashboard-suite {
            padding-bottom: 48px;
        }
        .admin-shell .dashboard-hero {
            padding-bottom: 18px;
        }
        .admin-shell .dashboard-hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(320px, 430px);
            gap: 1.5rem;
            align-items: stretch;
        }
        .admin-shell .dashboard-kpis {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .admin-shell .kpi-card,
        .admin-shell .insight-card,
        .admin-shell .dashboard-widget {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .12);
        }
        .admin-shell .kpi-card {
            min-height: 150px;
            border-radius: .75rem;
            padding: 1.15rem;
            color: #fff;
            box-shadow: 0 .5rem 1.5rem rgba(47, 43, 61, .12);
            isolation: isolate;
        }
        .admin-shell .kpi-card::before,
        .admin-shell .insight-card::before {
            content: "";
            position: absolute;
            inset: auto -12% -40% auto;
            width: 9rem;
            height: 9rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .18);
            filter: blur(2px);
            z-index: -1;
            animation: dashboardFloat 7s ease-in-out infinite alternate;
        }
        .admin-shell .kpi-card span,
        .admin-shell .insight-card span {
            display: block;
            color: rgba(255, 255, 255, .82);
            font-size: .8125rem;
            font-weight: 600;
        }
        .admin-shell .kpi-card strong,
        .admin-shell .insight-card strong {
            display: block;
            margin-top: .3rem;
            color: #fff;
            font-size: 2rem;
            line-height: 1;
            font-weight: 700;
        }
        .admin-shell .kpi-card small,
        .admin-shell .insight-card p {
            display: block;
            margin-top: .45rem;
            color: rgba(255, 255, 255, .78);
            font-size: .8125rem;
        }
        .admin-shell .kpi-card.violet,
        .admin-shell .gradient-violet {
            background: linear-gradient(135deg, #7367f0 0%, #8f85f4 100%) !important;
        }
        .admin-shell .kpi-card.cyan {
            background: linear-gradient(135deg, #00bad1 0%, #38d4e7 100%) !important;
        }
        .admin-shell .kpi-card.green {
            background: linear-gradient(135deg, #28c76f 0%, #65dc98 100%) !important;
        }
        .admin-shell .kpi-card.amber {
            background: linear-gradient(135deg, #ff9f43 0%, #ffc26b 100%) !important;
        }
        .admin-shell .sparkline {
            position: absolute;
            right: 1rem;
            bottom: 1rem;
            display: flex;
            align-items: end;
            gap: .28rem;
            height: 2.5rem;
        }
        .admin-shell .sparkline i {
            display: block;
            width: .42rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .5);
            animation: dashboardBars 1.8s ease-in-out infinite alternate;
        }
        .admin-shell .sparkline i:nth-child(1) { height: 38%; animation-delay: -.4s; }
        .admin-shell .sparkline i:nth-child(2) { height: 68%; animation-delay: -.8s; }
        .admin-shell .sparkline i:nth-child(3) { height: 48%; animation-delay: -.2s; }
        .admin-shell .sparkline i:nth-child(4) { height: 82%; animation-delay: -.6s; }
        .admin-shell .sparkline i:nth-child(5) { height: 58%; animation-delay: -1s; }
        .admin-shell .mini-progress,
        .admin-shell .bar-track {
            height: .45rem;
            overflow: hidden;
            border-radius: 999px;
            background: rgba(47, 43, 61, .08);
        }
        .admin-shell .kpi-card .mini-progress {
            position: absolute;
            right: 1rem;
            bottom: 1.3rem;
            left: 1rem;
            background: rgba(255, 255, 255, .24);
        }
        .admin-shell .mini-progress span,
        .admin-shell .bar-track span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: #7367f0;
            animation: dashboardGrow .8s cubic-bezier(.2,.8,.2,1) both;
        }
        .admin-shell .kpi-card .mini-progress span {
            background: rgba(255, 255, 255, .78);
        }
        .admin-shell .insight-card {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 1.25rem;
            align-items: center;
            min-height: 190px;
            border-radius: .875rem;
            padding: 1.35rem;
            color: #fff;
            box-shadow: 0 .75rem 2rem rgba(115, 103, 240, .28);
        }
        .admin-shell .orbital-chart,
        .admin-shell .experience-meter {
            --value: 0;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: conic-gradient(from -90deg, rgba(255,255,255,.95) calc(var(--value) * 1%), rgba(255,255,255,.22) 0);
            animation: dashboardPulse 3.5s ease-in-out infinite;
        }
        .admin-shell .orbital-chart {
            width: 7rem;
            height: 7rem;
        }
        .admin-shell .orbital-chart::before,
        .admin-shell .experience-meter::before {
            content: "";
            position: absolute;
            border-radius: 50%;
            background: inherit;
            opacity: .22;
            filter: blur(12px);
        }
        .admin-shell .orbital-chart span,
        .admin-shell .experience-meter span {
            display: grid;
            place-items: center;
            width: 5rem;
            height: 5rem;
            border-radius: 50%;
            background: rgba(115, 103, 240, .95);
            color: #fff;
            font-weight: 700;
        }
        .admin-shell .dashboard-mosaic {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, .9fr) minmax(280px, .7fr);
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .admin-shell .dashboard-widget {
            border-radius: .75rem;
            padding: 1.15rem;
        }
        .admin-shell .widget-heading {
            display: flex;
            align-items: start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .admin-shell .widget-heading h2,
        .admin-shell .dashboard-widget h2 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 600;
        }
        .admin-shell .widget-heading p {
            margin: .2rem 0 0;
            font-size: .8125rem;
        }
        .admin-shell .service-bars,
        .admin-shell .source-list {
            display: grid;
            gap: .9rem;
        }
        .admin-shell .service-bar,
        .admin-shell .source-row {
            display: grid;
            gap: .45rem;
        }
        .admin-shell .service-bar > div:first-child,
        .admin-shell .source-row {
            align-items: center;
            grid-template-columns: minmax(0, 1fr) auto;
        }
        .admin-shell .service-bar > div:first-child {
            display: grid;
        }
        .admin-shell .service-bar strong,
        .admin-shell .source-row strong {
            color: #444050;
            font-size: .9rem;
            font-weight: 600;
        }
        html.dark .admin-shell .service-bar strong,
        html.dark .admin-shell .source-row strong {
            color: #d5d1ea;
        }
        .admin-shell .service-bar span,
        .admin-shell .source-row span,
        .admin-shell .upcoming-list span {
            color: #6d6777;
            font-size: .8125rem;
        }
        html.dark .admin-shell .service-bar span,
        html.dark .admin-shell .source-row span,
        html.dark .admin-shell .upcoming-list span {
            color: #b6b1cb;
        }
        .admin-shell .service-bar:nth-child(4n+1) .bar-track span,
        .admin-shell .source-row:nth-child(4n+1) .mini-progress span { background: #7367f0; }
        .admin-shell .service-bar:nth-child(4n+2) .bar-track span,
        .admin-shell .source-row:nth-child(4n+2) .mini-progress span { background: #00bad1; }
        .admin-shell .service-bar:nth-child(4n+3) .bar-track span,
        .admin-shell .source-row:nth-child(4n+3) .mini-progress span { background: #28c76f; }
        .admin-shell .service-bar:nth-child(4n+4) .bar-track span,
        .admin-shell .source-row:nth-child(4n+4) .mini-progress span { background: #ff9f43; }
        .admin-shell .flow-chart {
            display: flex;
            align-items: end;
            gap: .7rem;
            min-height: 210px;
            padding: .75rem .25rem 0;
        }
        .admin-shell .flow-column {
            display: grid;
            grid-template-rows: auto 1fr auto;
            align-items: end;
            justify-items: center;
            gap: .45rem;
            flex: 1;
            min-width: 2rem;
            height: 190px;
        }
        .admin-shell .flow-column span,
        .admin-shell .flow-column small {
            color: #6d6777;
            font-size: .75rem;
            font-weight: 600;
        }
        html.dark .admin-shell .flow-column span,
        html.dark .admin-shell .flow-column small {
            color: #b6b1cb;
        }
        .admin-shell .flow-column i {
            display: block;
            width: 100%;
            min-height: .75rem;
            height: var(--height);
            border-radius: .55rem .55rem .2rem .2rem;
            background: linear-gradient(180deg, #7367f0, #00bad1);
            box-shadow: 0 .5rem 1.2rem rgba(115, 103, 240, .24);
            animation: dashboardRise .75s cubic-bezier(.2,.8,.2,1) both;
        }
        .admin-shell .experience-widget {
            display: grid;
            align-content: start;
        }
        .admin-shell .experience-meter {
            position: relative;
            width: 8rem;
            height: 8rem;
            margin: .5rem auto 1.2rem;
            background: conic-gradient(from -90deg, #28c76f calc(var(--value) * 1%), rgba(47, 43, 61, .08) 0);
        }
        .admin-shell .experience-meter span {
            width: 5.8rem;
            height: 5.8rem;
            background: #fff;
            color: #444050;
            font-size: 1.8rem;
        }
        html.dark .admin-shell .experience-meter span {
            background: #2f3349;
            color: #d5d1ea;
        }
        .admin-shell .status-grid {
            display: grid;
            gap: .55rem;
        }
        .admin-shell .status-pill {
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            gap: .75rem;
            border-radius: .55rem;
            padding: .55rem .65rem;
            background: rgba(47, 43, 61, .05);
        }
        html.dark .admin-shell .status-pill {
            background: rgba(225, 222, 245, .08);
        }
        .admin-shell .status-pill span,
        .admin-shell .status-pill strong {
            position: relative;
            z-index: 1;
            font-size: .8rem;
        }
        .admin-shell .status-pill i {
            position: absolute;
            inset: 0 auto 0 0;
            opacity: .18;
        }
        .admin-shell .status-pill.violet i,
        .admin-shell .status-badge.violet { background: #7367f0; color: #fff; }
        .admin-shell .status-pill.cyan i,
        .admin-shell .status-badge.cyan { background: #00bad1; color: #fff; }
        .admin-shell .status-pill.green i,
        .admin-shell .status-badge.green { background: #28c76f; color: #fff; }
        .admin-shell .status-pill.amber i,
        .admin-shell .status-badge.amber { background: #ff9f43; color: #fff; }
        .admin-shell .status-pill.red i,
        .admin-shell .status-badge.red { background: #ea5455; color: #fff; }
        .admin-shell .status-pill.slate i,
        .admin-shell .status-badge.slate { background: #6d6777; color: #fff; }
        .admin-shell .dashboard-bottom-grid {
            align-items: start;
        }
        .admin-shell .dashboard-booking-card {
            border-left-color: #7367f0;
        }
        .admin-shell .dashboard-actions a {
            min-height: 4rem;
            justify-content: start;
            border: 0;
            background: rgba(115, 103, 240, .08) !important;
            color: #7367f0;
        }
        .admin-shell .dashboard-actions a:nth-child(2) {
            background: rgba(0, 186, 209, .1) !important;
            color: #00bad1;
        }
        .admin-shell .dashboard-actions a:nth-child(3) {
            background: rgba(40, 199, 111, .1) !important;
            color: #28c76f;
        }
        .admin-shell .dashboard-actions a:nth-child(4) {
            background: rgba(255, 159, 67, .12) !important;
            color: #ff9f43;
        }
        .admin-shell .upcoming-list article {
            border-left: 3px solid #7367f0;
            border-radius: .5rem;
            padding: .65rem .75rem;
            background: rgba(47, 43, 61, .04);
        }
        html.dark .admin-shell .upcoming-list article {
            background: rgba(225, 222, 245, .06);
        }
        .admin-shell .setup-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .65rem;
            margin: 1rem 0;
        }
        .admin-shell .setup-grid span {
            display: grid;
            gap: .15rem;
            border-radius: .55rem;
            padding: .75rem;
            background: rgba(47, 43, 61, .05);
            color: #6d6777;
            font-size: .8rem;
        }
        html.dark .admin-shell .setup-grid span {
            background: rgba(225, 222, 245, .07);
            color: #b6b1cb;
        }
        .admin-shell .setup-grid strong {
            color: #444050;
            font-size: 1.1rem;
        }
        html.dark .admin-shell .setup-grid strong {
            color: #d5d1ea;
        }
        @keyframes dashboardFloat {
            from { transform: translate3d(0, 0, 0) scale(1); }
            to { transform: translate3d(-12%, -10%, 0) scale(1.12); }
        }
        @keyframes dashboardBars {
            from { transform: scaleY(.72); opacity: .65; }
            to { transform: scaleY(1.05); opacity: 1; }
        }
        @keyframes dashboardGrow {
            from { width: 0; }
        }
        @keyframes dashboardRise {
            from { height: 0; opacity: .4; }
        }
        @keyframes dashboardPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.025); }
        }
        @media (max-width: 1180px) {
            .admin-shell .dashboard-kpis,
            .admin-shell .dashboard-mosaic {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .admin-shell .experience-widget {
                grid-column: span 2;
            }
        }
        @media (max-width: 780px) {
            .admin-shell .dashboard-hero-grid,
            .admin-shell .dashboard-kpis,
            .admin-shell .dashboard-mosaic {
                grid-template-columns: 1fr;
            }
            .admin-shell .experience-widget {
                grid-column: auto;
            }
            .admin-shell .insight-card {
                grid-template-columns: 1fr;
            }
            .admin-shell .flow-chart {
                overflow-x: auto;
                justify-content: flex-start;
            }
            .admin-shell .flow-column {
                flex: 0 0 3rem;
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
                    @else
                        <img class="brand-logo resora-public-logo" src="{{ asset('images/resora-os-logo-sidebar.png') }}" alt="Resora OS">
                    @endif
                    <span class="brand-text">
                        <strong>{{ $venue->name ?? 'Resora OS' }}</strong>
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
                <small>Resora OS hospitality operations software by Code by Scott.</small>
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

            const adminDropdowns = document.querySelectorAll('.admin-topbar .admin-dropdown');
            const closeAdminDropdowns = (except = null) => {
                adminDropdowns.forEach((dropdown) => {
                    if (dropdown !== except) {
                        dropdown.open = false;
                    }
                });
            };

            adminDropdowns.forEach((dropdown) => {
                dropdown.addEventListener('toggle', () => {
                    if (dropdown.open) {
                        closeAdminDropdowns(dropdown);
                    }
                });

                dropdown.addEventListener('click', (event) => {
                    event.stopPropagation();
                });
            });

            document.addEventListener('click', () => {
                closeAdminDropdowns();
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeAdminDropdowns();
                }
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
