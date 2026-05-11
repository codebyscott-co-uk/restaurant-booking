<div class="form-grid">
    <div class="field full">
        <label for="name">Area name</label>
        <input id="name" name="name" value="{{ old('name', $area->name) }}" required>
    </div>
    <div class="field full">
        <label for="description">Description</label>
        <textarea id="description" name="description">{{ old('description', $area->description) }}</textarea>
    </div>
    <div class="field">
        <label for="sort_order">Sort order</label>
        <input id="sort_order" name="sort_order" type="number" min="0" max="999" value="{{ old('sort_order', $area->sort_order) }}" required>
    </div>
    <label style="display: flex; gap: 10px; align-items: center; font-weight: 700; align-self: end;">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $area->exists ? $area->is_active : true)) style="width: 18px; min-height: 18px;">
        Active area
    </label>
</div>

