<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $venue->widget_title ?: 'Book a table' }}</title>
    <style>
        :root {
            --ink: #111827;
            --muted: #62706d;
            --line: #dfe7e4;
            --paper: #f7f8f4;
            --primary: {{ $venue->primary_colour }};
            --accent: {{ $venue->accent_colour }};
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color: var(--ink); background: var(--paper); }
        .widget { min-height: 100vh; padding: 18px; background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 8%, white), #fff 44%, color-mix(in srgb, var(--accent) 8%, white)); }
        .card { max-width: 520px; margin: 0 auto; border: 1px solid var(--line); border-radius: 12px; background: rgba(255,255,255,.92); box-shadow: 0 20px 55px rgba(17,24,39,.12); overflow: hidden; }
        .head { padding: 20px; border-bottom: 1px solid var(--line); }
        .head img { display: block; width: 110px; max-width: 60%; height: auto; margin-bottom: 12px; }
        .eyebrow { color: var(--primary); font-size: 12px; font-weight: 900; letter-spacing: .08em; text-transform: uppercase; }
        h1 { margin: 6px 0 8px; font-size: 30px; line-height: 1; }
        p { color: var(--muted); line-height: 1.5; }
        form, .result { display: grid; gap: 12px; padding: 20px; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        label { display: grid; gap: 6px; font-size: 13px; font-weight: 800; }
        input, select, textarea, button { width: 100%; min-height: 46px; border: 1px solid var(--line); border-radius: 8px; padding: 11px 12px; font: inherit; }
        textarea { min-height: 86px; resize: vertical; }
        button { border: 0; background: linear-gradient(135deg, var(--primary), color-mix(in srgb, var(--primary) 72%, #2563eb)); color: #fff; font-weight: 900; cursor: pointer; }
        button:disabled { cursor: not-allowed; opacity: .55; }
        .slots { display: grid; grid-template-columns: repeat(auto-fit, minmax(86px, 1fr)); gap: 8px; }
        .slot { position: relative; }
        .slot input { position: absolute; opacity: 0; pointer-events: none; }
        .slot span { display: flex; min-height: 42px; align-items: center; justify-content: center; border: 1px solid var(--line); border-radius: 8px; background: #fff; font-weight: 900; }
        .slot input:checked + span { border-color: var(--primary); color: var(--primary); background: color-mix(in srgb, var(--primary) 10%, white); }
        .notice { border: 1px solid color-mix(in srgb, var(--accent) 42%, white); background: color-mix(in srgb, var(--accent) 12%, white); border-radius: 8px; padding: 12px; }
        .error { border-color: #fecaca; background: #fff7f7; color: #991b1b; }
        .full { grid-column: 1 / -1; }
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
