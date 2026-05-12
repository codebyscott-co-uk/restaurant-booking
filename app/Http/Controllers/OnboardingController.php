<?php

namespace App\Http\Controllers;

use App\Models\DiningArea;
use App\Models\OpeningHour;
use App\Models\RestaurantTable;
use App\Models\Service;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function create(): View
    {
        return view('auth.signup', [
            'venue' => Venue::first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ]);

        [$venue, $owner] = DB::transaction(function () use ($validated) {
            $venue = Venue::create([
                'name' => $validated['business_name'],
                'slug' => $this->uniqueVenueSlug($validated['business_name']),
                'contact_email' => $validated['contact_email'],
                'phone' => $validated['phone'] ?? null,
                'city' => $validated['city'] ?? null,
                'country' => 'United Kingdom',
                'minimum_lead_time_minutes' => 60,
                'maximum_advance_booking_days' => 60,
                'maximum_party_size' => 10,
                'maximum_covers_per_slot' => 30,
                'allow_joined_tables' => true,
                'cancellation_notice_hours' => 24,
                'timezone' => 'Europe/London',
                'primary_colour' => '#1f2933',
                'accent_colour' => '#c59b5b',
                'booking_terms' => 'Please tell us about allergies or special requirements before arrival.',
                'cancellation_policy' => 'Bookings can be changed or cancelled up to 24 hours before arrival.',
                'widget_enabled' => true,
                'widget_title' => 'Book a table',
                'widget_intro' => 'Choose your party size, date and service to reserve online.',
                'widget_button_text' => 'Book now',
            ]);

            $owner = User::create([
                'venue_id' => $venue->id,
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
                'password' => $validated['password'],
                'role' => 'owner',
                'job_title' => 'Owner',
                'is_active' => true,
            ]);

            $this->createStarterSetup($venue);
            $this->createStarterSubscription($venue);

            return [$venue, $owner];
        });

        Auth::login($owner);
        $request->session()->regenerate();

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Your '.$venue->name.' workspace is ready. Review your brand, policies and widget settings next.');
    }

    private function createStarterSubscription(Venue $venue): void
    {
        TenantSubscription::create([
            'venue_id' => $venue->id,
            'provider' => 'stripe',
            'plan' => 'starter',
            'status' => 'trialing',
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    private function createStarterSetup(Venue $venue): void
    {
        $area = DiningArea::create([
            'venue_id' => $venue->id,
            'name' => 'Main Dining Room',
            'description' => 'Default bookable area created during onboarding.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        foreach ([
            ['T1', 1, 2],
            ['T2', 2, 4],
            ['T3', 2, 4],
            ['T4', 4, 6],
        ] as [$name, $min, $max]) {
            RestaurantTable::create([
                'venue_id' => $venue->id,
                'dining_area_id' => $area->id,
                'name' => $name,
                'min_covers' => $min,
                'max_covers' => $max,
                'is_joinable' => $max <= 4,
                'is_active' => true,
            ]);
        }

        $services = [
            Service::create([
                'venue_id' => $venue->id,
                'name' => 'Lunch',
                'starts_at' => '12:00',
                'ends_at' => '15:00',
                'slot_interval_minutes' => 30,
                'default_duration_minutes' => 105,
                'min_covers' => 1,
                'max_covers' => 8,
                'requires_deposit' => false,
                'is_active' => true,
            ]),
            Service::create([
                'venue_id' => $venue->id,
                'name' => 'Dinner',
                'starts_at' => '17:30',
                'ends_at' => '22:00',
                'slot_interval_minutes' => 30,
                'default_duration_minutes' => 120,
                'min_covers' => 1,
                'max_covers' => 8,
                'requires_deposit' => false,
                'is_active' => true,
            ]),
        ];

        foreach ($services as $service) {
            for ($day = 0; $day <= 6; $day++) {
                OpeningHour::create([
                    'venue_id' => $venue->id,
                    'service_id' => $service->id,
                    'day_of_week' => $day,
                    'opens_at' => in_array($day, [0, 6], true) ? null : $service->starts_at,
                    'closes_at' => in_array($day, [0, 6], true) ? null : $service->ends_at,
                    'is_closed' => in_array($day, [0, 6], true),
                ]);
            }
        }
    }

    private function uniqueVenueSlug(string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'restaurant';
        $slug = $baseSlug;
        $counter = 2;

        while (Venue::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
