<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\DiningArea;
use App\Models\OpeningHour;
use App\Models\RestaurantTable;
use App\Models\Service;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $venue = Venue::create([
            'name' => 'The Demo Table',
            'slug' => 'the-demo-table',
            'contact_email' => 'bookings@demo-restaurant.test',
            'phone' => '020 7946 0123',
            'address_line_1' => '12 Market Street',
            'city' => 'London',
            'postcode' => 'SE1 9SG',
            'country' => 'United Kingdom',
            'website_url' => 'https://demo-restaurant.test',
            'minimum_lead_time_minutes' => 60,
            'maximum_advance_booking_days' => 60,
            'maximum_party_size' => 10,
            'maximum_covers_per_slot' => 28,
            'allow_joined_tables' => true,
            'cancellation_notice_hours' => 24,
            'timezone' => 'Europe/London',
            'primary_colour' => '#155e75',
            'accent_colour' => '#d97706',
            'booking_terms' => 'Please let us know about allergies before arrival. Tables are held for 15 minutes.',
            'cancellation_policy' => 'Bookings can be changed or cancelled up to 24 hours before arrival.',
        ]);

        User::factory()->create([
            'venue_id' => $venue->id,
            'name' => 'Restaurant Admin',
            'email' => 'hello@codebyscott.co.uk',
            'password' => Hash::make('Letmein.123@'),
            'role' => 'owner',
            'phone' => '07700 900123',
            'job_title' => 'Owner',
            'is_active' => true,
        ]);

        TenantSubscription::create([
            'venue_id' => $venue->id,
            'provider' => 'stripe',
            'plan' => 'starter',
            'status' => 'trialing',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $main = DiningArea::create([
            'venue_id' => $venue->id,
            'name' => 'Main Dining Room',
            'description' => 'Warm, flexible seating for everyday service.',
            'sort_order' => 1,
        ]);

        $terrace = DiningArea::create([
            'venue_id' => $venue->id,
            'name' => 'Terrace',
            'description' => 'Weather-friendly outside tables.',
            'sort_order' => 2,
        ]);

        foreach ([
            [$main, 'T1', 1, 2],
            [$main, 'T2', 2, 4],
            [$main, 'T3', 2, 4],
            [$main, 'T4', 4, 6],
            [$main, 'T5', 6, 8],
            [$terrace, 'O1', 2, 4],
            [$terrace, 'O2', 2, 4],
        ] as [$area, $name, $min, $max]) {
            RestaurantTable::create([
                'venue_id' => $venue->id,
                'dining_area_id' => $area->id,
                'name' => $name,
                'min_covers' => $min,
                'max_covers' => $max,
                'is_joinable' => $max <= 4,
            ]);
        }

        $lunch = Service::create([
            'venue_id' => $venue->id,
            'name' => 'Lunch',
            'starts_at' => '12:00',
            'ends_at' => '15:00',
            'slot_interval_minutes' => 30,
            'default_duration_minutes' => 105,
            'min_covers' => 1,
            'max_covers' => 8,
        ]);

        $dinner = Service::create([
            'venue_id' => $venue->id,
            'name' => 'Dinner',
            'starts_at' => '17:30',
            'ends_at' => '22:00',
            'slot_interval_minutes' => 30,
            'default_duration_minutes' => 120,
            'min_covers' => 1,
            'max_covers' => 8,
        ]);

        foreach ([$lunch, $dinner] as $service) {
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

        $customer = Customer::create([
            'venue_id' => $venue->id,
            'first_name' => 'Amelia',
            'last_name' => 'Hart',
            'email' => 'amelia@example.test',
            'phone' => '07123 456789',
            'notes' => 'Prefers a quieter table.',
            'is_vip' => true,
            'allergies' => 'Shellfish allergy.',
            'dietary_requirements' => 'Prefers gluten-free options when available.',
            'preferences' => 'Likes a quiet corner table and still water on arrival.',
            'favourite_dining_area_id' => $main->id,
            'favourite_restaurant_table_id' => RestaurantTable::where('name', 'T2')->value('id'),
        ]);

        $booking = Booking::create([
            'venue_id' => $venue->id,
            'customer_id' => $customer->id,
            'service_id' => $dinner->id,
            'booking_reference' => 'CBR'.strtoupper(fake()->bothify('####??')),
            'customer_manage_token' => fake()->sha256(),
            'party_size' => 4,
            'starts_at' => Carbon::today('Europe/London')->setTime(19, 0),
            'ends_at' => Carbon::today('Europe/London')->setTime(21, 0),
            'status' => 'confirmed',
            'source' => 'phone',
            'special_requests' => 'Anniversary dinner.',
            'confirmed_at' => now(),
        ]);

        $booking->tables()->attach(RestaurantTable::where('name', 'T2')->first());

        Booking::create([
            'venue_id' => $venue->id,
            'customer_id' => Customer::create([
                'venue_id' => $venue->id,
                'first_name' => 'Noah',
                'last_name' => 'Singh',
                'email' => 'noah@example.test',
                'phone' => '07999 111222',
                'dietary_requirements' => 'Vegetarian.',
                'preferences' => 'Enjoys terrace seating when weather allows.',
                'favourite_dining_area_id' => $terrace->id,
            ])->id,
            'service_id' => $lunch->id,
            'booking_reference' => 'CBR'.strtoupper(fake()->bothify('####??')),
            'customer_manage_token' => fake()->sha256(),
            'party_size' => 2,
            'starts_at' => Carbon::today('Europe/London')->setTime(12, 30),
            'ends_at' => Carbon::today('Europe/London')->setTime(14, 15),
            'status' => 'seated',
            'source' => 'web',
            'confirmed_at' => now(),
        ])->tables()->attach(RestaurantTable::where('name', 'T1')->first());

        foreach ([
            ['Sofia', 'Reed', 'sofia@example.test', 'completed', 'staff', $lunch, 3, 13, 30, 'Prefers sparkling water.'],
            ['Ethan', 'Cole', 'ethan@example.test', 'cancelled', 'phone', $dinner, 2, 18, 30, null],
            ['Maya', 'Brooks', 'maya@example.test', 'no_show', 'web', $dinner, 2, 20, 0, null],
        ] as [$first, $last, $email, $status, $source, $service, $partySize, $hour, $minute, $notes]) {
            $seedBooking = Booking::create([
                'venue_id' => $venue->id,
                'customer_id' => Customer::create([
                    'venue_id' => $venue->id,
                    'first_name' => $first,
                    'last_name' => $last,
                    'email' => $email,
                'phone' => '07700 '.fake()->numerify('######'),
                'notes' => $notes,
                'preferences' => $notes,
                'is_vip' => $first === 'Sofia',
            ])->id,
                'service_id' => $service->id,
                'booking_reference' => 'CBR'.strtoupper(fake()->bothify('####??')),
                'customer_manage_token' => fake()->sha256(),
                'party_size' => $partySize,
                'starts_at' => Carbon::today('Europe/London')->setTime($hour, $minute),
                'ends_at' => Carbon::today('Europe/London')->setTime($hour, $minute)->addMinutes($service->default_duration_minutes),
                'status' => $status,
                'source' => $source,
                'internal_notes' => $status === 'cancelled' ? 'Cancelled by phone.' : null,
                'confirmed_at' => in_array($status, ['confirmed', 'seated', 'completed'], true) ? now() : null,
                'cancelled_at' => $status === 'cancelled' ? now() : null,
            ]);

            $seedBooking->tables()->attach(RestaurantTable::whereIn('name', $partySize > 2 ? ['T3'] : ['O1'])->first());
        }

        Booking::create([
            'venue_id' => $venue->id,
            'customer_id' => $customer->id,
            'service_id' => $lunch->id,
            'booking_reference' => 'CBR'.strtoupper(fake()->bothify('####??')),
            'customer_manage_token' => fake()->sha256(),
            'party_size' => 2,
            'starts_at' => Carbon::today('Europe/London')->subDays(18)->setTime(13, 0),
            'ends_at' => Carbon::today('Europe/London')->subDays(18)->setTime(14, 45),
            'status' => 'completed',
            'source' => 'web',
            'special_requests' => 'Window table if available.',
            'confirmed_at' => now()->subDays(18),
        ])->tables()->attach(RestaurantTable::where('name', 'T2')->first());
    }
}
