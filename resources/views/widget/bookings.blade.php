<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $venue->widget_title ?: 'Book a table' }}</title>
    <style>
        :root {
            --ink: #28231c;
            --muted: #6e665b;
            --line: rgba(72, 58, 39, .14);
            --paper: #f7f1e8;
            --panel: rgba(255, 251, 244, .84);
            --primary: color-mix(in srgb, {{ $venue->primary_colour }} 58%, #7a5a32);
            --accent: color-mix(in srgb, {{ $venue->accent_colour }} 72%, #d0a85f);
            --gold: #b98a42;
            --gold-soft: rgba(185, 138, 66, .18);
            --glass-border: rgba(116, 91, 53, .16);
            --glass-highlight: rgba(255,255,255,.72);
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color: var(--ink); background: var(--paper); }
        .widget { position: relative; min-height: 100vh; padding: 18px; overflow: hidden; background: radial-gradient(circle at 10% 8%, rgba(255,255,255,.9), transparent 30%), radial-gradient(circle at 82% 18%, color-mix(in srgb, var(--gold) 18%, transparent), transparent 34%), radial-gradient(circle at 48% 92%, rgba(230, 199, 151, .28), transparent 36%), linear-gradient(180deg, #fff9ef 0%, #f7f1e8 48%, #efe4d2 100%); }
        .widget::before { content: ""; position: fixed; inset: -34% -28%; pointer-events: none; opacity: .88; background: linear-gradient(118deg, transparent 0 16%, rgba(255,255,255,.82) 21%, rgba(245, 222, 181, .5) 27%, transparent 35% 100%), linear-gradient(132deg, transparent 0 30%, rgba(223, 184, 116, .46) 36%, rgba(255, 248, 232, .72) 43%, transparent 54% 100%), linear-gradient(104deg, transparent 0 52%, rgba(237, 206, 156, .44) 58%, rgba(255,255,255,.58) 64%, transparent 74% 100%), radial-gradient(38% 32% at 14% 18%, rgba(255,255,255,.9), transparent 68%), radial-gradient(32% 30% at 76% 12%, color-mix(in srgb, var(--gold) 34%, transparent), transparent 70%), conic-gradient(from 132deg at 52% 45%, transparent 0 20%, rgba(255,255,255,.42) 32%, color-mix(in srgb, var(--gold) 24%, transparent) 48%, transparent 66% 100%); background-size: 120% 120%, 132% 132%, 116% 116%, 100% 100%, 100% 100%, 100% 100%; filter: blur(22px) saturate(1.14); transform: translate3d(0,0,0); animation: widgetAmbientGlass 18s ease-in-out infinite alternate; }
        .card { position: relative; z-index: 1; max-width: 520px; margin: 0 auto; border: 1px solid var(--glass-border); border-radius: 16px; background: var(--panel); box-shadow: 0 24px 70px rgba(40,31,20,.14), inset 0 1px var(--glass-highlight); overflow: hidden; backdrop-filter: blur(28px) saturate(1.22); }
        .card::before { content: ""; position: absolute; inset: 0; pointer-events: none; background: radial-gradient(circle at 18% 0%, rgba(255,255,255,.64), transparent 28%), radial-gradient(circle at 86% 8%, var(--gold-soft), transparent 26%); opacity: .8; }
        .head { padding: 20px; border-bottom: 1px solid var(--line); }
        .head img { display: block; width: 110px; max-width: 60%; height: auto; margin-bottom: 12px; border-radius: 8px; background: #fffaf1; padding: 6px; }
        .eyebrow { color: var(--primary); font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        h1 { margin: 6px 0 8px; font-size: 30px; line-height: 1; color: var(--ink); }
        p { color: var(--muted); line-height: 1.5; }
        form, .result { display: grid; gap: 12px; padding: 20px; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        label { display: grid; gap: 6px; color: #4b4033; font-size: 12px; font-weight: 760; }
        input, select, textarea, button { width: 100%; min-height: 42px; border: 1px solid var(--line); border-radius: 10px; padding: 10px 12px; font: inherit; }
        input, select, textarea { background: rgba(255,255,255,.76); color: var(--ink); box-shadow: inset 0 1px rgba(255,255,255,.76); }
        textarea { min-height: 86px; resize: vertical; }
        button { position: relative; overflow: hidden; isolation: isolate; border-color: color-mix(in srgb, var(--gold) 44%, var(--glass-border)); background: color-mix(in srgb, #fff8ea 82%, var(--gold) 18%); color: #312617; font-weight: 850; cursor: pointer; box-shadow: 0 14px 30px color-mix(in srgb, var(--gold) 16%, transparent), inset 0 1px rgba(255,255,255,.84), inset 0 -1px rgba(116,91,53,.08); transition: transform .18s cubic-bezier(.2,.8,.2,1), border-color .18s ease, box-shadow .18s ease, background .18s ease; }
        button::before { content: ""; position: absolute; inset: 0; z-index: -1; background: radial-gradient(circle at 24% 0%, rgba(255,255,255,.78), transparent 32%), radial-gradient(circle at 82% 100%, var(--gold-soft), transparent 34%); opacity: .8; transition: transform .22s ease, opacity .22s ease; }
        button:hover { transform: translateY(-1px); border-color: color-mix(in srgb, var(--gold) 54%, var(--glass-border)); background: rgba(255,251,244,.86); box-shadow: 0 16px 34px rgba(40,31,20,.11), inset 0 1px rgba(255,255,255,.86), 0 0 0 3px color-mix(in srgb, var(--gold) 10%, transparent); }
        button:hover::before { opacity: 1; transform: translate3d(0,-1px,0) scale(1.08); }
        button:active { transform: translateY(0) scale(.985); box-shadow: 0 8px 18px rgba(40,31,20,.08), inset 0 2px 5px rgba(116,91,53,.12); }
        button:disabled { cursor: not-allowed; opacity: .55; }
        .slots { display: grid; grid-template-columns: repeat(auto-fit, minmax(86px, 1fr)); gap: 8px; }
        .slot { position: relative; }
        .slot input { position: absolute; opacity: 0; pointer-events: none; }
        .slot span { display: flex; min-height: 42px; align-items: center; justify-content: center; border: 1px solid var(--glass-border); border-radius: 10px; background: rgba(255,251,244,.68); color: var(--ink); font-weight: 800; box-shadow: inset 0 1px var(--glass-highlight); transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease, background .18s ease; }
        .slot:hover span { transform: translateY(-1px); border-color: color-mix(in srgb, var(--gold) 34%, var(--glass-border)); background: rgba(255,251,244,.86); box-shadow: 0 12px 28px rgba(40,31,20,.1), inset 0 1px var(--glass-highlight); }
        .slot input:checked + span { border-color: color-mix(in srgb, var(--gold) 46%, var(--glass-border)); color: color-mix(in srgb, var(--gold) 70%, var(--ink)); background: color-mix(in srgb, var(--gold) 13%, rgba(255,251,244,.86)); box-shadow: 0 14px 30px color-mix(in srgb, var(--gold) 13%, transparent), inset 0 1px var(--glass-highlight); }
        .notice { border: 1px solid color-mix(in srgb, var(--accent) 30%, var(--line)); background: color-mix(in srgb, var(--accent) 12%, white); color: #4b3924; border-radius: 10px; padding: 12px; }
        .error { border-color: rgba(166, 41, 41, .2); background: #fff7f7; color: #9c2020; }
        .full { grid-column: 1 / -1; }
        @keyframes widgetAmbientGlass {
            0% { background-position: 0% 12%, 100% 0%, 18% 100%, 0 0, 0 0, 0 0; transform: translate3d(-3%, -2%, 0) rotate(-4deg) scale(1.02); }
            50% { background-position: 28% 4%, 72% 18%, 44% 78%, 0 0, 0 0, 0 0; transform: translate3d(2.5%, 2%, 0) rotate(3deg) scale(1.07); }
            100% { background-position: 46% 18%, 48% 34%, 72% 58%, 0 0, 0 0, 0 0; transform: translate3d(4%, -2.5%, 0) rotate(-2deg) scale(1.1); }
        }
        :root {
            --ink: #2f3349;
            --muted: #6f7285;
            --line: rgba(47, 51, 73, .12);
            --paper: #f5f6fb;
            --panel: #ffffff;
            --primary: color-mix(in srgb, {{ $venue->primary_colour }} 35%, #7367f0);
            --accent: color-mix(in srgb, {{ $venue->accent_colour }} 35%, #00bad1);
            --shadow: 0 10px 30px rgba(47, 51, 73, .12);
        }
        body {
            color: var(--ink);
            background: var(--paper);
        }
        .widget {
            background:
                radial-gradient(circle at 12% 4%, rgba(115, 103, 240, .14), transparent 28%),
                radial-gradient(circle at 88% 12%, rgba(0, 186, 209, .1), transparent 26%),
                linear-gradient(180deg, #f5f6fb 0%, #eef0f8 100%);
        }
        .widget::before,
        .card::before,
        button::before {
            display: none;
        }
        .card {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: var(--panel);
            box-shadow: var(--shadow);
            backdrop-filter: none;
        }
        .head {
            padding: 22px;
            border-bottom-color: var(--line);
            background: linear-gradient(135deg, rgba(115, 103, 240, .08), transparent);
        }
        .head img {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 10px;
        }
        .eyebrow {
            color: var(--primary);
            font-size: 11px;
            font-weight: 750;
            letter-spacing: .08em;
        }
        h1 {
            font-size: 28px;
            line-height: 1.08;
            color: var(--ink);
        }
        p {
            color: var(--muted);
            font-size: 13px;
        }
        form, .result {
            padding: 22px;
        }
        label {
            color: var(--ink);
            font-size: 12.5px;
            font-weight: 650;
        }
        input, select, textarea, button {
            min-height: 40px;
            border-color: var(--line);
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 13px;
        }
        input, select, textarea {
            background: #fff;
            color: var(--ink);
            box-shadow: none;
        }
        input:focus, select:focus, textarea:focus {
            outline: 0;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--primary) 18%, transparent);
        }
        button {
            border-color: transparent;
            background: linear-gradient(135deg, var(--primary), #5e50ee);
            color: #fff;
            font-weight: 700;
            box-shadow: 0 8px 24px color-mix(in srgb, var(--primary) 32%, transparent);
        }
        button:hover {
            transform: translateY(-1px);
            background: linear-gradient(135deg, #8378f3, var(--primary));
            box-shadow: 0 10px 28px color-mix(in srgb, var(--primary) 38%, transparent);
        }
        .slot span {
            min-height: 38px;
            border-color: var(--line);
            border-radius: 8px;
            background: #f8f8fc;
            color: var(--ink);
            font-size: 12.5px;
            font-weight: 650;
            box-shadow: none;
        }
        .slot:hover span {
            border-color: color-mix(in srgb, var(--primary) 28%, var(--line));
            background: color-mix(in srgb, var(--primary) 12%, white);
            color: var(--primary);
            box-shadow: none;
        }
        .slot input:checked + span {
            border-color: transparent;
            background: var(--primary);
            color: #fff;
            box-shadow: 0 6px 18px color-mix(in srgb, var(--primary) 30%, transparent);
        }
        .notice {
            border-color: color-mix(in srgb, var(--accent) 28%, var(--line));
            background: color-mix(in srgb, var(--accent) 12%, white);
            color: color-mix(in srgb, var(--accent) 70%, var(--ink));
        }
        .error {
            border-color: rgba(234, 84, 85, .2);
            background: rgba(234, 84, 85, .12);
            color: #ea5455;
        }
        :root {
            --gold: #7367f0;
            --gold-soft: rgba(115, 103, 240, .14);
            --glass-border: rgba(47, 43, 61, .12);
            --glass-highlight: transparent;
        }
        .widget::before,
        .card::before,
        button::before {
            display: none !important;
            content: none !important;
        }
        .widget {
            background:
                radial-gradient(circle at 12% 4%, rgba(115, 103, 240, .14), transparent 28%),
                radial-gradient(circle at 88% 12%, rgba(0, 186, 209, .1), transparent 26%),
                linear-gradient(180deg, #f8f7fa 0%, #f1f0f6 100%);
        }
        button,
        button:hover {
            border-color: transparent;
            background: #7367f0;
            color: #fff;
            box-shadow: 0 .125rem .375rem rgba(115, 103, 240, .35);
        }
        button:hover {
            background: #685dd8;
        }
        .slot:hover span {
            border-color: transparent;
            background: rgba(115, 103, 240, .12);
            color: #7367f0;
            box-shadow: none;
        }
        .slot input:checked + span {
            border-color: transparent;
            background: #7367f0;
            color: #fff;
            box-shadow: 0 .125rem .375rem rgba(115, 103, 240, .28);
        }
        @media (prefers-reduced-motion: reduce) { .widget::before { animation: none; } }
        @media (max-width: 520px) { .grid { grid-template-columns: 1fr; } .widget { padding: 10px; } }
    </style>
</head>
<body>
    <div class="widget">
        <div class="card">
            <div class="head">
                @if ($venue->logo_url)
                    <img src="{{ $venue->logo_url }}" alt="{{ $venue->name }}">
                @endif
                <div class="eyebrow">{{ $venue->name }}</div>
                <h1>{{ $venue->widget_title ?: 'Book a table' }}</h1>
                <p>{{ $venue->widget_intro ?: 'Choose a date, party size and service to reserve your table online.' }}</p>
            </div>

            <form data-widget-form>
                <div data-message></div>
                <div class="grid">
                    <label>Party size
                        <input name="party_size" type="number" min="1" max="{{ $venue->maximum_party_size }}" value="2" required>
                    </label>
                    <label>Date
                        <input name="date" type="date" value="{{ today($venue->timezone)->addDay()->toDateString() }}" required>
                    </label>
                    <label class="full">Service
                        <select name="service_id" required></select>
                    </label>
                </div>
                <div class="slots" data-slots></div>
                <div class="grid">
                    <label>First name
                        <input name="first_name" autocomplete="given-name" required>
                    </label>
                    <label>Last name
                        <input name="last_name" autocomplete="family-name" required>
                    </label>
                    <label>Email
                        <input name="email" type="email" autocomplete="email" required>
                    </label>
                    <label>Phone
                        <input name="phone" autocomplete="tel" required>
                    </label>
                    <label class="full">Special requests
                        <textarea name="special_requests"></textarea>
                    </label>
                </div>
                <button type="submit">{{ $venue->widget_button_text ?: 'Book a table' }}</button>
            </form>
        </div>
    </div>

    <script>
        (() => {
            const form = document.querySelector('[data-widget-form]');
            const serviceSelect = form.querySelector('[name="service_id"]');
            const slots = document.querySelector('[data-slots]');
            const message = document.querySelector('[data-message]');
            const apiBase = @json($apiBase ?? url('/api/v1'));
            const api = (path, options = {}) => fetch(apiBase + path, Object.assign({
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
            }, options)).then(async (response) => {
                const data = await response.json();
                if (!response.ok) throw data;
                return data;
            });

            const setMessage = (html, type = 'notice') => {
                message.innerHTML = html ? '<div class="' + type + '">' + html + '</div>' : '';
            };

            const loadServices = async () => {
                const response = await api('/services');
                serviceSelect.innerHTML = response.data.map((service) => `<option value="${service.id}">${service.name} · ${service.starts_at} to ${service.ends_at}</option>`).join('');
                await loadSlots();
            };

            const loadSlots = async () => {
                const params = new URLSearchParams({
                    service_id: serviceSelect.value,
                    date: form.date.value,
                    party_size: form.party_size.value,
                });
                slots.innerHTML = '';
                setMessage('');

                if (!serviceSelect.value) return;

                try {
                    const response = await api('/availability?' + params.toString());
                    if (!response.data.slots.length) {
                        setMessage('No available times for this selection.');
                        return;
                    }

                    slots.innerHTML = response.data.slots.map((slot, index) => `
                        <label class="slot">
                            <input type="radio" name="time" value="${slot.time}" ${index === 0 ? 'checked' : ''}>
                            <span>${slot.time}</span>
                        </label>
                    `).join('');
                } catch (error) {
                    setMessage('Please adjust your party size, date or service.', 'notice error');
                }
            };

            form.addEventListener('change', (event) => {
                if (['service_id', 'date', 'party_size'].includes(event.target.name)) {
                    loadSlots();
                }
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const button = form.querySelector('button[type="submit"]');
                button.disabled = true;
                setMessage('');

                try {
                    const formData = new FormData(form);
                    const payload = Object.fromEntries(formData.entries());
                    const response = await api('/bookings', {
                        method: 'POST',
                        body: JSON.stringify(payload),
                    });
                    form.innerHTML = `<div class="result"><div class="notice"><strong>Booking confirmed.</strong><p>Your reference is ${response.data.booking_reference}.</p><p><a href="${response.data.manage_url}" target="_blank" rel="noopener">Manage booking</a></p></div></div>`;
                } catch (error) {
                    const firstError = error.errors ? Object.values(error.errors)[0][0] : 'We could not create that booking. Please try again.';
                    setMessage(firstError, 'notice error');
                    button.disabled = false;
                }
            });

            loadServices();
        })();
    </script>
</body>
</html>
