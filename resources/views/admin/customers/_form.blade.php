@php
    $isEdit = $customer->exists;
@endphp

<section class="shell customer-crm-suite">
    <form class="panel customer-form-card" method="post" action="{{ $action }}">
        @csrf
        @if ($method !== 'post')
            @method($method)
        @endif

        <div class="widget-heading">
            <div>
                <h2>{{ $isEdit ? 'Edit customer profile' : 'Create customer profile' }}</h2>
                <p>Keep guest details, preferences and private staff notes in one tenant-safe profile.</p>
            </div>
            <a class="button subtle" href="{{ $isEdit ? route('admin.customers.show', $customer) : route('admin.customers.index') }}">Back</a>
        </div>

        @if ($errors->any())
            <div class="panel errors" style="margin-bottom: 14px;">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0 0 4px;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="customer-form-section">
            <h3>Contact details</h3>
            <div class="form-grid">
                <div class="field">
                    <label for="first_name">First name</label>
                    <input id="first_name" name="first_name" value="{{ old('first_name', $customer->first_name) }}" required>
                </div>
                <div class="field">
                    <label for="last_name">Last name</label>
                    <input id="last_name" name="last_name" value="{{ old('last_name', $customer->last_name) }}" required>
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $customer->email) }}" required>
                </div>
                <div class="field">
                    <label for="phone">Phone</label>
                    <input id="phone" name="phone" value="{{ old('phone', $customer->phone) }}" required>
                </div>
                <label class="crm-check">
                    <input type="checkbox" name="is_vip" value="1" @checked(old('is_vip', $customer->is_vip))>
                    <span>VIP guest</span>
                </label>
                <label class="crm-check">
                    <input type="checkbox" name="marketing_opt_in" value="1" @checked(old('marketing_opt_in', $customer->marketing_opt_in))>
                    <span>Marketing opt-in</span>
                </label>
            </div>
        </div>

        <div class="customer-form-section">
            <h3>Guest intelligence</h3>
            <div class="form-grid">
                <div class="field full">
                    <label for="allergies">Allergies</label>
                    <textarea id="allergies" name="allergies" placeholder="Shellfish allergy, nut allergy, gluten sensitivity...">{{ old('allergies', $customer->allergies) }}</textarea>
                </div>
                <div class="field full">
                    <label for="dietary_requirements">Dietary requirements</label>
                    <textarea id="dietary_requirements" name="dietary_requirements" placeholder="Vegetarian, vegan, halal, low-sodium...">{{ old('dietary_requirements', $customer->dietary_requirements) }}</textarea>
                </div>
                <div class="field full">
                    <label for="preferences">Preferences</label>
                    <textarea id="preferences" name="preferences" placeholder="Prefers booth seating, quiet area, sparkling water on arrival...">{{ old('preferences', $customer->preferences) }}</textarea>
                </div>
                <div class="field">
                    <label for="favourite_dining_area_id">Favourite area</label>
                    <select id="favourite_dining_area_id" name="favourite_dining_area_id">
                        <option value="">No favourite area</option>
                        @foreach ($venue->diningAreas as $area)
                            <option value="{{ $area->id }}" @selected((int) old('favourite_dining_area_id', $customer->favourite_dining_area_id) === $area->id)>{{ $area->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="favourite_restaurant_table_id">Favourite table</label>
                    <select id="favourite_restaurant_table_id" name="favourite_restaurant_table_id">
                        <option value="">No favourite table</option>
                        @foreach ($venue->diningAreas as $area)
                            @foreach ($area->tables as $table)
                                <option value="{{ $table->id }}" @selected((int) old('favourite_restaurant_table_id', $customer->favourite_restaurant_table_id) === $table->id)>{{ $table->name }} · {{ $area->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="customer-form-section">
            <h3>Internal notes</h3>
            <div class="field">
                <label for="notes">Private staff notes</label>
                <textarea id="notes" name="notes" rows="6" placeholder="Only visible to staff with CRM access.">{{ old('notes', $customer->notes) }}</textarea>
            </div>
        </div>

        <button class="primary full" type="submit">{{ $submitLabel }}</button>
    </form>
</section>
