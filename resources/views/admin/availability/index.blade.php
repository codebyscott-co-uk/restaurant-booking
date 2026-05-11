@extends('layouts.app', ['title' => 'Availability', 'venue' => $venue])

@section('content')
<section class="hero">
    <div class="shell">
        <div class="eyebrow">Availability</div>
        <h1>Opening hours and closures</h1>
        <p>Control when each service is bookable and block holidays, private events or maintenance periods.</p>
    </div>
</section>

<section class="shell grid booking-grid" style="padding-bottom: 48px;">
    <form class="panel" method="post" action="{{ route('admin.availability.hours.update') }}">
        @csrf
        @method('put')

        @if (session('status'))
            <div class="panel success" style="margin-bottom: 14px;"><p style="margin: 0;">{{ session('status') }}</p></div>
        @endif

        @if ($errors->any())
            <div class="panel errors" style="margin-bottom: 14px;">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0 0 4px;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <h2>Weekly hours</h2>
        <div class="grid">
            @foreach ($venue->services as $service)
                <div class="panel">
                    <h3>{{ $service->name }}</h3>
                    <div class="grid" style="gap: 10px;">
                        @foreach ($dayNames as $day => $dayName)
                            @php($hour = $service->openingHours->firstWhere('day_of_week', $day))
                            <div class="form-grid" style="align-items: end;">
                                <input type="hidden" name="hours[{{ $service->id }}][{{ $day }}][id]" value="{{ $hour->id }}">
                                <div class="field">
                                    <label>{{ $dayName }}</label>
                                    <label style="display: flex; gap: 10px; align-items: center; font-weight: 700; min-height: 46px;">
                                        <input type="checkbox" name="hours[{{ $service->id }}][{{ $day }}][is_closed]" value="1" @checked($hour->is_closed) style="width: 18px; min-height: 18px;">
                                        Closed
                                    </label>
                                </div>
                                <div class="field">
                                    <label for="opens-{{ $service->id }}-{{ $day }}">Opens</label>
                                    <input id="opens-{{ $service->id }}-{{ $day }}" name="hours[{{ $service->id }}][{{ $day }}][opens_at]" type="time" value="{{ $hour->opens_at ? substr($hour->opens_at, 0, 5) : '' }}">
                                </div>
                                <div class="field">
                                    <label for="closes-{{ $service->id }}-{{ $day }}">Closes</label>
                                    <input id="closes-{{ $service->id }}-{{ $day }}" name="hours[{{ $service->id }}][{{ $day }}][closes_at]" type="time" value="{{ $hour->closes_at ? substr($hour->closes_at, 0, 5) : '' }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        <button class="primary" type="submit" style="margin-top: 18px;">Save opening hours</button>
    </form>

    <aside class="panel">
        <h2>Add closure</h2>
        <form method="post" action="{{ route('admin.availability.closures.store') }}" class="grid">
            @csrf
            <div class="field">
                <label for="service_id">Service</label>
                <select id="service_id" name="service_id">
                    <option value="">All services</option>
                    @foreach ($venue->services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="starts_at">Starts</label>
                <input id="starts_at" name="starts_at" type="datetime-local" required>
            </div>
            <div class="field">
                <label for="ends_at">Ends</label>
                <input id="ends_at" name="ends_at" type="datetime-local" required>
            </div>
            <div class="field">
                <label for="reason">Reason</label>
                <input id="reason" name="reason" placeholder="Private event, holiday, maintenance">
            </div>
            <button class="primary" type="submit">Add closure</button>
        </form>

        <h2 style="margin-top: 28px;">Closures</h2>
        <div class="staff-list">
            @forelse ($venue->closures->sortBy('starts_at') as $closure)
                <article class="panel">
                    <h3>{{ $closure->reason ?: 'Closure' }}</h3>
                    <p style="margin: 0;">
                        {{ $closure->starts_at->format('D j M H:i') }} to {{ $closure->ends_at->format('D j M H:i') }}
                    </p>
                    <div class="table-list">
                        <span class="badge">{{ $closure->service?->name ?: 'All services' }}</span>
                    </div>
                    <form method="post" action="{{ route('admin.availability.closures.destroy', $closure) }}" style="margin-top: 12px;" onsubmit="return confirm('Remove this closure?');">
                        @csrf
                        @method('delete')
                        <button type="submit">Remove</button>
                    </form>
                </article>
            @empty
                <div class="notice"><strong>No closures yet.</strong></div>
            @endforelse
        </div>
    </aside>
</section>
@endsection

