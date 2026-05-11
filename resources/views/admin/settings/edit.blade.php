@extends('layouts.app', ['title' => 'Business settings', 'venue' => $venue])

@section('content')
<section class="hero compact">
    <div class="shell">
        <div class="eyebrow">Settings</div>
        <h1>Business settings</h1>
        <p>Manage the restaurant identity, location, booking rules and branded email copy from focused panels.</p>
    </div>
</section>

<section class="shell grid settings-grid" style="padding-bottom: 48px;">
    <form class="settings-form" method="post" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('put')

        @if (session('status'))
            <div class="panel success"><p style="margin: 0;">{{ session('status') }}</p></div>
        @endif

        @if ($errors->any())
            <div class="panel errors">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0 0 4px;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="settings-tabs" role="tablist" aria-label="Settings sections">
            <button class="settings-tab active" type="button" data-settings-tab="identity" role="tab" aria-selected="true">Identity</button>
            <button class="settings-tab" type="button" data-settings-tab="location" role="tab" aria-selected="false">Location</button>
            <button class="settings-tab" type="button" data-settings-tab="policies" role="tab" aria-selected="false">Booking policies</button>
            <button class="settings-tab" type="button" data-settings-tab="emails" role="tab" aria-selected="false">Email templates</button>
        </div>

        <section class="panel settings-panel" data-settings-panel="identity" role="tabpanel">
            <div class="settings-panel-header">
                <div>
                    <h2>Identity</h2>
                    <p>Control the public brand, logo and contact details guests see.</p>
                </div>
                <span class="badge">Brand</span>
            </div>
            <div class="form-grid">
                <div class="field full">
                    <label for="name">Business name</label>
                    <input id="name" name="name" value="{{ old('name', $venue->name) }}" required>
                </div>
                <div class="field">
                    <label for="contact_email">Contact email</label>
                    <input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $venue->contact_email) }}">
                </div>
                <div class="field">
                    <label for="phone">Phone</label>
                    <input id="phone" name="phone" value="{{ old('phone', $venue->phone) }}">
                </div>
                <div class="field full">
                    <label for="website_url">Website</label>
                    <input id="website_url" name="website_url" type="url" value="{{ old('website_url', $venue->website_url) }}" placeholder="https://example.com">
                </div>
                <div class="field">
                    <label for="primary_colour">Primary colour</label>
                    <input id="primary_colour" name="primary_colour" type="color" value="{{ old('primary_colour', $venue->primary_colour) }}" required>
                </div>
                <div class="field">
                    <label for="accent_colour">Accent colour</label>
                    <input id="accent_colour" name="accent_colour" type="color" value="{{ old('accent_colour', $venue->accent_colour) }}" required>
                </div>
                <div class="field full">
                    <label for="logo">Business logo</label>
                    <input id="logo" name="logo" type="file" accept="image/*">
                </div>
                @if ($venue->logo_url)
                    <label class="full" style="display: flex; gap: 10px; align-items: center; font-weight: 700;">
                        <input type="checkbox" name="remove_logo" value="1" style="width: 18px; min-height: 18px;">
                        Remove current logo
                    </label>
                @endif
            </div>
        </section>

        <section class="panel settings-panel" data-settings-panel="location" role="tabpanel" hidden>
            <div class="settings-panel-header">
                <div>
                    <h2>Location</h2>
                    <p>Set the venue address and timezone used for booking calculations.</p>
                </div>
                <span class="badge">Venue</span>
            </div>
            <div class="form-grid">
                <div class="field full">
                    <label for="address_line_1">Address line 1</label>
                    <input id="address_line_1" name="address_line_1" value="{{ old('address_line_1', $venue->address_line_1) }}">
                </div>
                <div class="field full">
                    <label for="address_line_2">Address line 2</label>
                    <input id="address_line_2" name="address_line_2" value="{{ old('address_line_2', $venue->address_line_2) }}">
                </div>
                <div class="field">
                    <label for="city">Town or city</label>
                    <input id="city" name="city" value="{{ old('city', $venue->city) }}">
                </div>
                <div class="field">
                    <label for="county">County</label>
                    <input id="county" name="county" value="{{ old('county', $venue->county) }}">
                </div>
                <div class="field">
                    <label for="postcode">Postcode</label>
                    <input id="postcode" name="postcode" value="{{ old('postcode', $venue->postcode) }}">
                </div>
                <div class="field">
                    <label for="country">Country</label>
                    <input id="country" name="country" value="{{ old('country', $venue->country) }}">
                </div>
                <div class="field full">
                    <label for="timezone">Timezone</label>
                    <input id="timezone" name="timezone" value="{{ old('timezone', $venue->timezone) }}" required>
                </div>
            </div>
        </section>

        <section class="panel settings-panel" data-settings-panel="policies" role="tabpanel" hidden>
            <div class="settings-panel-header">
                <div>
                    <h2>Booking policies</h2>
                    <p>Fine-tune the rules guests and staff use when creating bookings.</p>
                </div>
                <span class="badge">Rules</span>
            </div>
            <div class="form-grid">
                <div class="field">
                    <label for="minimum_lead_time_minutes">Minimum lead time</label>
                    <input id="minimum_lead_time_minutes" name="minimum_lead_time_minutes" type="number" min="0" max="10080" value="{{ old('minimum_lead_time_minutes', $venue->minimum_lead_time_minutes) }}" required>
                </div>
                <div class="field">
                    <label for="maximum_advance_booking_days">Advance booking window</label>
                    <input id="maximum_advance_booking_days" name="maximum_advance_booking_days" type="number" min="1" max="730" value="{{ old('maximum_advance_booking_days', $venue->maximum_advance_booking_days) }}" required>
                </div>
                <div class="field">
                    <label for="maximum_party_size">Maximum party size</label>
                    <input id="maximum_party_size" name="maximum_party_size" type="number" min="1" max="99" value="{{ old('maximum_party_size', $venue->maximum_party_size) }}" required>
                </div>
                <div class="field">
                    <label for="maximum_covers_per_slot">Maximum covers per slot</label>
                    <input id="maximum_covers_per_slot" name="maximum_covers_per_slot" type="number" min="1" max="999" value="{{ old('maximum_covers_per_slot', $venue->maximum_covers_per_slot) }}">
                </div>
                <div class="field">
                    <label for="cancellation_notice_hours">Online change/cancel notice</label>
                    <input id="cancellation_notice_hours" name="cancellation_notice_hours" type="number" min="0" max="720" value="{{ old('cancellation_notice_hours', $venue->cancellation_notice_hours) }}" required>
                </div>
                <label class="full" style="display: flex; gap: 10px; align-items: center; font-weight: 700;">
                    <input type="checkbox" name="allow_joined_tables" value="1" @checked(old('allow_joined_tables', $venue->allow_joined_tables)) style="width: 18px; min-height: 18px;">
                    Allow joined tables for larger parties
                </label>
                <div class="field full">
                    <label for="booking_terms">Booking terms</label>
                    <textarea id="booking_terms" name="booking_terms">{{ old('booking_terms', $venue->booking_terms) }}</textarea>
                </div>
                <div class="field full">
                    <label for="cancellation_policy">Cancellation policy</label>
                    <textarea id="cancellation_policy" name="cancellation_policy">{{ old('cancellation_policy', $venue->cancellation_policy) }}</textarea>
                </div>
            </div>
        </section>

        <section class="panel settings-panel" data-settings-panel="emails" role="tabpanel" hidden>
            <div class="settings-panel-header">
                <div>
                    <h2>Email templates</h2>
                    <p>Customise the main message in each branded email. Booking details and buttons are added automatically.</p>
                </div>
                <span class="badge">Email</span>
            </div>
            <div class="form-grid">
                @foreach ([
                    'email_confirmation_content' => ['Confirmation email', '<p>Thanks for booking with us. We have your reservation and look forward to welcoming you.</p>'],
                    'email_modification_content' => ['Modification email', '<p>Your booking has been updated. Please check the latest details below.</p>'],
                    'email_cancellation_content' => ['Cancellation email', '<p>Your booking has been cancelled. We hope to welcome you another time.</p>'],
                    'email_reminder_content' => ['Reminder email', '<p>This is a friendly reminder about your upcoming reservation. We look forward to seeing you soon.</p>'],
                    'email_staff_alert_content' => ['Staff alert email', '<p>A new booking has arrived. Review the guest details and allocated tables below.</p>'],
                    'email_footer_content' => ['Email footer note', '<p>Online changes and cancellations close before arrival according to your booking policy.</p>'],
                ] as $field => [$label, $fallback])
                    @php($value = old($field, $venue->{$field} ?: $fallback))
                    <div class="field full">
                        <label for="{{ $field }}">{{ $label }}</label>
                        <div class="editor-toolbar" aria-label="{{ $label }} formatting">
                            <button type="button" data-editor-command="bold"><strong>B</strong></button>
                            <button type="button" data-editor-command="italic"><em>I</em></button>
                            <button type="button" data-editor-command="insertUnorderedList">List</button>
                        </div>
                        <div class="wysiwyg-editor" contenteditable="true" data-editor="#{{ $field }}">{!! $value !!}</div>
                        <textarea id="{{ $field }}" name="{{ $field }}" hidden>{{ $value }}</textarea>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="settings-save">
            <button class="primary" type="submit">Save settings</button>
        </div>
    </form>

    <aside class="grid">
        <div class="panel">
            <h2>Brand preview</h2>
            @if ($venue->logo_url)
                <img class="logo-preview" src="{{ $venue->logo_url }}" alt="{{ $venue->name }} logo">
            @else
                <div class="notice"><strong>No logo uploaded yet.</strong></div>
            @endif
            <p><strong>{{ $venue->name }}</strong></p>
            <p>{{ $venue->address_line_1 }} {{ $venue->city }} {{ $venue->postcode }}</p>
            <div class="table-list">
                <span class="badge" style="border-color: var(--primary); color: var(--primary);">Primary</span>
                <span class="badge" style="border-color: var(--accent); color: var(--accent);">Accent</span>
            </div>
        </div>

        <div class="panel">
            <h2>Settings health</h2>
            <div class="table-list">
                <span class="badge">{{ $venue->contact_email ? 'Email set' : 'Email missing' }}</span>
                <span class="badge">{{ $venue->logo_url ? 'Logo set' : 'No logo' }}</span>
                <span class="badge">{{ $venue->cancellation_notice_hours }}h notice</span>
            </div>
            <p style="margin-bottom: 0;">Use the tabs to focus on one area at a time, then save all changes together.</p>
        </div>
    </aside>
</section>
@endsection
