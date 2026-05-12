@php
    $isEdit = $booking->exists;
    $selectedTableIds = collect(old('table_ids', $isEdit ? $booking->tables->pluck('id')->all() : $availableTables->pluck('id')->all()))->map(fn ($id) => (int) $id);
@endphp

<section class="shell booking-form-suite">
    <form class="panel booking-availability-card" method="get" action="{{ $isEdit ? route('admin.bookings.edit', $booking) : route('admin.bookings.create') }}">
        <div class="widget-heading">
            <div>
                <h2>Find availability</h2>
                <p>Choose the service, date, time and party size first to surface suitable tables.</p>
            </div>
            <a class="button subtle" href="{{ route('admin.diary', ['date' => $selectedDate->toDateString()]) }}">Back to diary</a>
        </div>
        <div class="form-grid">
            <div class="field">
                <label for="party_size_lookup">Party size</label>
                <input id="party_size_lookup" name="party_size" type="number" min="1" max="99" value="{{ old('party_size', $partySize) }}">
            </div>
            <div class="field">
                <label for="date_lookup">Date</label>
                <input id="date_lookup" name="date" type="date" value="{{ old('date', $selectedDate->toDateString()) }}">
            </div>
            <div class="field">
                <label for="time_lookup">Time</label>
                <input id="time_lookup" name="time" type="time" value="{{ old('time', $isEdit ? $booking->starts_at->format('H:i') : request('time')) }}">
            </div>
            <div class="field">
                <label for="service_lookup">Service</label>
                <select id="service_lookup" name="service_id">
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" @selected((int) old('service_id', $selectedService?->id) === $service->id)>
                            {{ $service->name }} · {{ substr($service->starts_at, 0, 5) }} to {{ substr($service->ends_at, 0, 5) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button class="primary full" type="submit">Refresh availability</button>
        </div>
    </form>

    <form class="panel booking-edit-card" method="post" action="{{ $action }}">
        @csrf
        @if ($method !== 'post')
            @method($method)
        @endif

        <div class="widget-heading">
            <div>
                <h2>{{ $isEdit ? 'Booking details' : 'Create booking' }}</h2>
                <p>{{ old('party_size', $partySize) }} guests · {{ old('date', $selectedDate->format('Y-m-d')) }} · {{ $selectedService?->name }}</p>
            </div>
            @if ($isEdit)
                <span class="badge">{{ $booking->booking_reference }}</span>
            @endif
        </div>

        @if ($errors->any())
            <div class="panel errors" style="margin-bottom: 14px;">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0 0 4px;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <input type="hidden" name="service_id" value="{{ old('service_id', $selectedService?->id) }}">
        <input type="hidden" name="party_size" value="{{ old('party_size', $partySize) }}">
        <input type="hidden" name="date" value="{{ old('date', $selectedDate->toDateString()) }}">

        <div class="booking-form-section">
            <h3>Time</h3>
            @if ($slots->isEmpty())
                <div class="notice">
                    <strong>No standard available times for this selection.</strong>
                    <p style="margin-bottom: 0;">Staff can still review settings, closures and service hours, then refresh availability.</p>
                </div>
            @else
                <div class="slots" aria-label="Available times">
                    @foreach ($slots as $slot)
                        <label class="slot">
                            <input type="radio" name="time" value="{{ $slot->format('H:i') }}" @checked(old('time', $isEdit ? $booking->starts_at->format('H:i') : null) === $slot->format('H:i') || ($loop->first && ! old('time') && ! $isEdit))>
                            <span>{{ $slot->format('H:i') }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="booking-form-section">
            <h3>Customer details</h3>
            <div class="form-grid">
                <div class="field">
                    <label for="first_name">First name</label>
                    <input id="first_name" name="first_name" value="{{ old('first_name', $booking->customer->first_name ?? '') }}" required>
                </div>
                <div class="field">
                    <label for="last_name">Last name</label>
                    <input id="last_name" name="last_name" value="{{ old('last_name', $booking->customer->last_name ?? '') }}" required>
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $booking->customer->email ?? '') }}">
                </div>
                <div class="field">
                    <label for="phone">Phone</label>
                    <input id="phone" name="phone" value="{{ old('phone', $booking->customer->phone ?? '') }}" required>
                </div>
                <div class="field full">
                    <label for="customer_notes">Customer notes</label>
                    <textarea id="customer_notes" name="customer_notes">{{ old('customer_notes', $booking->customer->notes ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="booking-form-section">
            <h3>Booking controls</h3>
            <div class="form-grid">
                <div class="field">
                    <label for="source">Source</label>
                    <select id="source" name="source">
                        @foreach (['phone' => 'Phone', 'walk_in' => 'Walk-in', 'staff' => 'Staff', 'web' => 'Online'] as $source => $label)
                            <option value="{{ $source }}" @selected(old('source', $booking->source ?: 'phone') === $source)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        @foreach (\App\Models\Booking::STATUSES as $status)
                            <option value="{{ $status }}" @selected(old('status', $booking->status ?: 'confirmed') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field full">
                    <label for="special_requests">Guest requests</label>
                    <textarea id="special_requests" name="special_requests">{{ old('special_requests', $booking->special_requests) }}</textarea>
                </div>
                <div class="field full">
                    <label for="internal_notes">Internal notes</label>
                    <textarea id="internal_notes" name="internal_notes">{{ old('internal_notes', $booking->internal_notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="booking-form-section">
            <h3>Table assignment</h3>
            @if ($venue->diningAreas->isEmpty())
                <div class="empty-state compact">
                    <strong>No dining areas configured.</strong>
                    <p style="margin: 0;">Create dining areas and tables before assigning bookings.</p>
                </div>
            @else
                <div class="table-assignment-grid">
                    @foreach ($venue->diningAreas as $area)
                        <div class="table-assignment-area">
                            <strong>{{ $area->name }}</strong>
                            @forelse ($area->tables as $table)
                                @php($capacityWarning = $table->max_covers < old('party_size', $partySize))
                                <label class="table-choice {{ $capacityWarning ? 'warning' : '' }}">
                                    <input type="checkbox" name="table_ids[]" value="{{ $table->id }}" @checked($selectedTableIds->contains($table->id))>
                                    <span>
                                        {{ $table->name }}
                                        <small>{{ $table->min_covers }}-{{ $table->max_covers }} covers{{ $capacityWarning ? ' · below party size' : '' }}</small>
                                    </span>
                                </label>
                            @empty
                                <p class="muted">No tables in this area.</p>
                            @endforelse
                        </div>
                    @endforeach
                </div>
                @if ($availableTables->isNotEmpty())
                    <p class="muted">Suggested assignment: {{ $availableTables->map(fn ($table) => $table->name.' · '.$table->diningArea->name)->join(', ') }}.</p>
                @endif
            @endif
        </div>

        <button class="primary full" type="submit" @disabled($slots->isEmpty() && ! $isEdit)>{{ $submitLabel }}</button>
    </form>
</section>
