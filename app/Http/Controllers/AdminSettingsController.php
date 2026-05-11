<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'venue' => Venue::firstOrFail(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $venue = Venue::firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'county' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'minimum_lead_time_minutes' => ['required', 'integer', 'min:0', 'max:10080'],
            'maximum_advance_booking_days' => ['required', 'integer', 'min:1', 'max:730'],
            'maximum_party_size' => ['required', 'integer', 'min:1', 'max:99'],
            'maximum_covers_per_slot' => ['nullable', 'integer', 'min:1', 'max:999'],
            'allow_joined_tables' => ['nullable', 'boolean'],
            'cancellation_notice_hours' => ['required', 'integer', 'min:0', 'max:720'],
            'timezone' => ['required', 'string', 'max:255'],
            'primary_colour' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_colour' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'booking_terms' => ['nullable', 'string', 'max:4000'],
            'cancellation_policy' => ['nullable', 'string', 'max:4000'],
            'email_confirmation_content' => ['nullable', 'string', 'max:6000'],
            'email_modification_content' => ['nullable', 'string', 'max:6000'],
            'email_cancellation_content' => ['nullable', 'string', 'max:6000'],
            'email_reminder_content' => ['nullable', 'string', 'max:6000'],
            'email_staff_alert_content' => ['nullable', 'string', 'max:6000'],
            'email_footer_content' => ['nullable', 'string', 'max:6000'],
            'widget_enabled' => ['nullable', 'boolean'],
            'widget_title' => ['nullable', 'string', 'max:255'],
            'widget_intro' => ['nullable', 'string', 'max:1000'],
            'widget_button_text' => ['required', 'string', 'max:80'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['allow_joined_tables'] = $request->boolean('allow_joined_tables');
        $validated['widget_enabled'] = $request->boolean('widget_enabled');

        foreach ([
            'email_confirmation_content',
            'email_modification_content',
            'email_cancellation_content',
            'email_reminder_content',
            'email_staff_alert_content',
            'email_footer_content',
        ] as $field) {
            $validated[$field] = $this->cleanEmailHtml($validated[$field] ?? null);
        }

        if ($request->boolean('remove_logo') && $venue->logo_path) {
            Storage::disk('public')->delete($venue->logo_path);
            $validated['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($venue->logo_path) {
                Storage::disk('public')->delete($venue->logo_path);
            }

            $validated['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        unset($validated['logo'], $validated['remove_logo']);

        $venue->update($validated);

        return back()->with('status', 'Business settings updated.');
    }

    private function cleanEmailHtml(?string $html): ?string
    {
        if (! $html) {
            return null;
        }

        $html = strip_tags($html, '<p><br><strong><b><em><i><ul><ol><li><a>');
        $html = preg_replace('/\s*on\w+="[^"]*"/i', '', $html);
        $html = preg_replace("/\s*on\w+='[^']*'/i", '', $html);
        $html = preg_replace('/href=("|\')javascript:[^"\']*("|\')/i', 'href="#"', $html);

        return trim($html) ?: null;
    }
}
