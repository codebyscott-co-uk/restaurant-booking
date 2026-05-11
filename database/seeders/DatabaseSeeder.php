<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\DiningArea;
use App\Models\RestaurantTable;
use App\Models\Service;
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
        User::factory()->create([
            'name' => 'Restaurant Admin',
            'email' => 'hello@codebyscott.co.uk',
            'password' => Hash::make('Letmein.123@'),
            'role' => 'owner',
            'phone' => '07700 900123',
            'job_title' => 'Owner',
            'is_active' => true,
        ]);

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
            'timezone' => 'Europe/London',
            'primary_colour' => '#155e75',
            'accent_colour' => '#d97706',
            'booking_terms' => 'Please let us know about allergies before arrival. Tables are held for 15 minutes.',
            'cancellation_policy' => 'Bookings can be changed or cancelled up to 24 hours before arrival.',
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

        $customer = Customer::create([
            'first_name' => 'Amelia',
            'last_name' => 'Hart',
            'email' => 'amelia@example.test',
            'phone' => '07123 456789',
            'notes' => 'Prefers a quieter table.',
        ]);

        $booking = Booking::create([
            'venue_id' => $venue->id,
            'customer_id' => $customer->id,
            'service_id' => $dinner->id,
            'booking_reference' => 'CBR'.strtoupper(fake()->bothify('####??')),
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
                'first_name' => 'Noah',
                'last_name' => 'Singh',
                'email' => 'noah@example.test',
                'phone' => '07999 111222',
            ])->id,
            'service_id' => $lunch->id,
            'booking_reference' => 'CBR'.strtoupper(fake()->bothify('####??')),
            'party_size' => 2,
            'starts_at' => Carbon::today('Europe/London')->setTime(12, 30),
            'ends_at' => Carbon::today('Europe/London')->setTime(14, 15),
            'status' => 'seated',
            'source' => 'web',
            'confirmed_at' => now(),
        ])->tables()->attach(RestaurantTable::where('name', 'T1')->first());
    }
}
