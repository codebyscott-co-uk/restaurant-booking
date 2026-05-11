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
        .widget { position: relative; min-height: 100vh; padding: 18px; overflow: hidden; background: linear-gradient(180deg, #fffaf1 0%, #f7f1e8 100%); }
        .widget::before { content: ""; position: fixed; inset: -24% -18%; pointer-events: none; opacity: .44; background: radial-gradient(42% 34% at 18% 18%, rgba(255,255,255,.72), transparent 62%), radial-gradient(34% 28% at 74% 14%, color-mix(in srgb, var(--gold) 20%, transparent), transparent 68%), radial-gradient(32% 36% at 58% 76%, rgba(255,255,255,.52), transparent 66%), radial-gradient(30% 26% at 16% 84%, color-mix(in srgb, var(--accent) 14%, transparent), transparent 68%); filter: blur(26px) saturate(1.05); transform: translate3d(0,0,0); animation: widgetAmbientGlass 28s ease-in-out infinite alternate; }
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
            0% { transform: translate3d(-1.5%, -1%, 0) rotate(-2deg) scale(1); }
            50% { transform: translate3d(1.5%, 1%, 0) rotate(2deg) scale(1.025); }
            100% { transform: translate3d(2.5%, -1.5%, 0) rotate(-1deg) scale(1.04); }
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
