<div class="form-grid">
    <div class="field full">
        <label for="name">Service name</label>
        <input id="name" name="name" value="{{ old('name', $service->name) }}" required>
    </div>
    <div class="field">
        <label for="starts_at">Starts at</label>
        <input id="starts_at" name="starts_at" type="time" value="{{ old('starts_at', substr((string) $service->starts_at, 0, 5)) }}" required>
    </div>
    <div class="field">
        <label for="ends_at">Ends at</label>
        <input id="ends_at" name="ends_at" type="time" value="{{ old('ends_at', substr((string) $service->ends_at, 0, 5)) }}" required>
    </div>
    <div class="field">
        <label for="slot_interval_minutes">Slot interval</label>
        <select id="slot_interval_minutes" name="slot_interval_minutes" required>
            @foreach ([15, 30, 45, 60] as $minutes)
                <option value="{{ $minutes }}" @selected((int) old('slot_interval_minutes', $service->slot_interval_minutes) === $minutes)>{{ $minutes }} minutes</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label for="default_duration_minutes">Booking duration</label>
        <input id="default_duration_minutes" name="default_duration_minutes" type="number" min="30" max="360" step="15" value="{{ old('default_duration_minutes', $service->default_duration_minutes) }}" required>
    </div>
    <div class="field">
        <label for="min_covers">Minimum guests</label>
        <input id="min_covers" name="min_covers" type="number" min="1" max="99" value="{{ old('min_covers', $service->min_covers) }}" required>
    </div>
    <div class="field">
        <label for="max_covers">Maximum guests</label>
        <input id="max_covers" name="max_covers" type="number" min="1" max="99" value="{{ old('max_covers', $service->max_covers) }}" required>
    </div>
    <label style="display: flex; gap: 10px; align-items: center; font-weight: 700;">
        <input type="checkbox" name="requires_deposit" value="1" @checked(old('requires_deposit', $service->requires_deposit)) style="width: 18px; min-height: 18px;">
        Requires deposit
    </label>
    <label style="display: flex; gap: 10px; align-items: center; font-weight: 700;">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $service->exists ? $service->is_active : true)) style="width: 18px; min-height: 18px;">
        Active for online booking
    </label>
</div>
