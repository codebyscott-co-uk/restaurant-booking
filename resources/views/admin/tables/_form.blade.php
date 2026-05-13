<div class="form-grid">
    <div class="field full">
        <label for="name">Table name</label>
        <input id="name" name="name" value="{{ old('name', $table->name) }}" required>
    </div>
    <div class="field full">
        <label for="dining_area_id">Dining area</label>
        <select id="dining_area_id" name="dining_area_id" required>
            @foreach ($areas as $area)
                <option value="{{ $area->id }}" @selected((int) old('dining_area_id', $table->dining_area_id) === $area->id)>{{ $area->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label for="min_covers">Minimum guests</label>
        <input id="min_covers" name="min_covers" type="number" min="1" max="99" value="{{ old('min_covers', $table->min_covers) }}" required>
    </div>
    <div class="field">
        <label for="max_covers">Maximum guests</label>
        <input id="max_covers" name="max_covers" type="number" min="1" max="99" value="{{ old('max_covers', $table->max_covers) }}" required>
    </div>
    <div class="field full">
        <label for="internal_notes">Internal notes</label>
        <textarea id="internal_notes" name="internal_notes" placeholder="Operational notes, access needs, visibility, awkward placement...">{{ old('internal_notes', $table->internal_notes) }}</textarea>
    </div>
    <label style="display: flex; gap: 10px; align-items: center; font-weight: 700;">
        <input type="checkbox" name="is_joinable" value="1" @checked(old('is_joinable', $table->is_joinable)) style="width: 18px; min-height: 18px;">
        Can be joined with other tables
    </label>
    <label style="display: flex; gap: 10px; align-items: center; font-weight: 700;">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $table->exists ? $table->is_active : true)) style="width: 18px; min-height: 18px;">
        Active for online booking
    </label>
</div>
