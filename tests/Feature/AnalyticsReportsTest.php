<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\DiningArea;
use App\Models\RestaurantTable;
use App\Models\Service;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Cashier\Subscription;
use Tests\TestCase;

class AnalyticsReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'resora_billing.plans.starter.stripe_price_id' => 'price_starter_test',
            'resora_billing.plans.professional.stripe_price_id' => 'price_professional_test',
            'resora_billing.plans.premium.stripe_price_id' => 'price_premium_test',
        ]);
    }

    public function test_starter_cannot_access_analytics_and_sees_upgrade_prompt(): void
    {
        [$venue, $user] = $this->tenant('Starter Venue');

        $this->actingAs($user)
            ->get('/admin/reports')
            ->assertRedirect('/admin/upgrade/analytics');

        $this->actingAs($user)
            ->get('/admin/upgrade/analytics')
            ->assertOk()
            ->assertSee('Analytics')
            ->assertSee('Professional');
    }

    public function test_professional_can_access_standard_analytics(): void
    {
        [$venue, $user] = $this->tenant('Professional Venue');
        $this->subscribe($venue, 'price_professional_test');
        $this->booking($venue, 'Ada Standard', now()->subDays(2), 4, 'confirmed');

        $this->actingAs($user)
            ->get('/admin/reports?range=last_7_days')
            ->assertOk()
            ->assertSeeText('Analytics & reports')
            ->assertSee('Total bookings')
            ->assertSee('Ada Standard')
            ->assertSee('Locked: Export bookings');
    }

    public function test_professional_cannot_access_premium_exports(): void
    {
        [$venue, $user] = $this->tenant('Professional Venue');
        $this->subscribe($venue, 'price_professional_test');
        $this->booking($venue, 'Export Locked', now(), 2);

        $this->actingAs($user)
            ->get('/admin/reports/export/bookings')
            ->assertRedirect('/admin/upgrade/advanced_reporting');
    }

    public function test_premium_can_access_reports_and_csv_exports(): void
    {
        [$venue, $user] = $this->tenant('Premium Venue');
        $this->subscribe($venue, 'price_premium_test');
        $this->booking($venue, 'Priya Premium', now()->subDay(), 5, 'completed');

        $this->actingAs($user)
            ->get('/admin/reports?range=last_7_days')
            ->assertOk()
            ->assertSee('Forecasted covers')
            ->assertSee('Export bookings')
            ->assertDontSee('Locked: Export bookings');

        $response = $this->actingAs($user)
            ->get('/admin/reports/export/bookings?range=last_7_days')
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->assertStringContainsString('Priya Premium', $response->streamedContent());
    }

    public function test_analytics_data_is_tenant_scoped(): void
    {
        [$firstVenue, $firstUser] = $this->tenant('First Venue');
        [$secondVenue] = $this->tenant('Second Venue');
        $this->subscribe($firstVenue, 'price_professional_test');
        $this->booking($firstVenue, 'First Guest', now()->subDay(), 2, 'confirmed');
        $this->booking($secondVenue, 'Second Guest', now()->subDay(), 8, 'confirmed');

        $this->actingAs($firstUser)
            ->get('/admin/reports?range=last_7_days')
            ->assertOk()
            ->assertSee('First Guest')
            ->assertSee('2')
            ->assertDontSee('Second Guest');
    }

    public function test_date_filters_apply_to_report_data(): void
    {
        [$venue, $user] = $this->tenant('Filtered Venue');
        $this->subscribe($venue, 'price_professional_test');
        $this->booking($venue, 'Inside Range', now()->subDays(3), 3, 'confirmed');
        $this->booking($venue, 'Outside Range', now()->subDays(20), 6, 'confirmed');

        $this->actingAs($user)
            ->get('/admin/reports?range=last_7_days')
            ->assertOk()
            ->assertSee('Inside Range')
            ->assertDontSee('Outside Range');
    }

    public function test_csv_export_is_tenant_scoped_for_premium(): void
    {
        [$firstVenue, $firstUser] = $this->tenant('First Premium Venue');
        [$secondVenue] = $this->tenant('Second Premium Venue');
        $this->subscribe($firstVenue, 'price_premium_test');
        $this->booking($firstVenue, 'Tenant Export', now(), 2);
        $this->booking($secondVenue, 'Hidden Export', now(), 7);

        $response = $this->actingAs($firstUser)
            ->get('/admin/reports/export/bookings?range=last_7_days')
            ->assertOk();

        $this->assertStringContainsString('Tenant Export', $response->streamedContent());
        $this->assertStringNotContainsString('Hidden Export', $response->streamedContent());
    }

    private function tenant(string $name): array
    {
        $venue = Venue::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'timezone' => 'Europe/London',
        ]);

        $user = User::create([
            'venue_id' => $venue->id,
            'name' => $name.' Owner',
            'email' => Str::slug($name).'@example.test',
            'password' => 'password',
            'role' => 'owner',
            'is_active' => true,
        ]);

        return [$venue, $user];
    }

    private function subscribe(Venue $venue, string $price): Subscription
    {
        return Subscription::create([
            'venue_id' => $venue->id,
            'type' => 'default',
            'stripe_id' => 'sub_'.Str::random(24),
            'stripe_status' => 'active',
            'stripe_price' => $price,
            'quantity' => 1,
        ]);
    }

    private function booking(Venue $venue, string $guest, $startsAt, int $partySize, string $status = 'confirmed'): Booking
    {
        $service = Service::firstOrCreate([
            'venue_id' => $venue->id,
            'name' => 'Dinner',
        ], [
            'starts_at' => '18:00',
            'ends_at' => '22:00',
            'slot_interval_minutes' => 30,
            'default_duration_minutes' => 90,
            'min_covers' => 1,
            'max_covers' => 8,
            'is_active' => true,
        ]);

        $table = RestaurantTable::create([
            'venue_id' => $venue->id,
            'dining_area_id' => DiningArea::firstOrCreate([
                'venue_id' => $venue->id,
                'name' => 'Main dining room',
            ], [
                'is_active' => true,
                'sort_order' => 1,
            ])->id,
            'name' => 'T'.Str::random(4),
            'min_covers' => 1,
            'max_covers' => 8,
            'is_joinable' => true,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'venue_id' => $venue->id,
            'first_name' => Str::before($guest, ' ') ?: $guest,
            'last_name' => Str::after($guest, ' ') ?: 'Guest',
            'email' => Str::slug($guest).'@example.test',
            'phone' => '07123456789',
        ]);

        $booking = Booking::create([
            'venue_id' => $venue->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'booking_reference' => 'TST'.Str::upper(Str::random(8)),
            'customer_manage_token' => Str::random(40),
            'party_size' => $partySize,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMinutes(90),
            'status' => $status,
            'source' => 'web',
            'confirmed_at' => now(),
        ]);

        $booking->tables()->attach($table);

        return $booking;
    }
}
