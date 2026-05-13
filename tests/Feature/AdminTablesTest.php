<?php

namespace Tests\Feature;

use App\Models\DiningArea;
use App\Models\Booking;
use App\Models\RestaurantTable;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminTablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_manage_tables(): void
    {
        $this->seed();

        $this->get('/admin/areas')->assertRedirect('/staff/login');
    }

    public function test_staff_can_create_and_update_dining_area(): void
    {
        $this->seed();
        $admin = User::first();

        $this->actingAs($admin)
            ->post('/admin/areas', [
                'name' => 'Private Dining',
                'description' => 'Bookable private room.',
                'sort_order' => 3,
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/areas');

        $area = DiningArea::where('name', 'Private Dining')->firstOrFail();

        $this->actingAs($admin)
            ->put('/admin/areas/'.$area->id, [
                'name' => 'Private Room',
                'description' => 'Updated private room.',
                'sort_order' => 4,
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/areas');

        $this->assertDatabaseHas('dining_areas', [
            'id' => $area->id,
            'name' => 'Private Room',
            'sort_order' => 4,
        ]);
    }

    public function test_staff_can_create_and_update_table(): void
    {
        $this->seed();
        $admin = User::first();
        $area = DiningArea::firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/tables', [
                'dining_area_id' => $area->id,
                'name' => 'P1',
                'min_covers' => 2,
                'max_covers' => 6,
                'is_joinable' => '1',
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/areas');

        $table = RestaurantTable::where('name', 'P1')->firstOrFail();

        $this->actingAs($admin)
            ->put('/admin/tables/'.$table->id, [
                'dining_area_id' => $area->id,
                'name' => 'P2',
                'min_covers' => 1,
                'max_covers' => 4,
                'is_joinable' => '',
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/areas');

        $this->assertDatabaseHas('restaurant_tables', [
            'id' => $table->id,
            'name' => 'P2',
            'min_covers' => 1,
            'max_covers' => 4,
            'is_joinable' => false,
        ]);
    }

    public function test_dining_area_with_tables_cannot_be_deleted(): void
    {
        $this->seed();
        $area = DiningArea::where('name', 'Main Dining Room')->firstOrFail();

        $this->actingAs(User::first())
            ->delete('/admin/areas/'.$area->id)
            ->assertRedirect();

        $this->assertDatabaseHas('dining_areas', [
            'id' => $area->id,
            'name' => 'Main Dining Room',
        ]);
    }

    public function test_table_with_bookings_cannot_be_deleted(): void
    {
        $this->seed();
        $table = RestaurantTable::where('name', 'T1')->firstOrFail();
        $booking = Booking::firstOrFail()->replicate(['booking_reference', 'customer_manage_token']);
        $booking->booking_reference = 'CBRFUTURE';
        $booking->customer_manage_token = 'future-token';
        $booking->starts_at = Carbon::now('Europe/London')->addWeek()->setTime(18, 0);
        $booking->ends_at = Carbon::now('Europe/London')->addWeek()->setTime(20, 0);
        $booking->save();
        $booking->tables()->attach($table);

        $this->actingAs(User::first())
            ->delete('/admin/tables/'.$table->id)
            ->assertRedirect();

        $this->assertDatabaseHas('restaurant_tables', [
            'id' => $table->id,
            'name' => 'T1',
        ]);
    }

    public function test_tables_page_loads_with_core_summary_for_starter(): void
    {
        $this->seed();

        $this->actingAs(User::first())
            ->get('/admin/areas')
            ->assertOk()
            ->assertSee('Floor setup')
            ->assertSee('Active capacity')
            ->assertSee('Visual Floorplan')
            ->assertSee('Upgrade for floorplan tools');
    }

    public function test_table_list_view_loads(): void
    {
        $this->seed();

        $this->actingAs(User::first())
            ->get('/admin/areas?display=list')
            ->assertOk()
            ->assertSee('Table list')
            ->assertSee('Future bookings');
    }

    public function test_one_venue_cannot_manage_another_venues_table_or_area(): void
    {
        $this->seed();
        $user = User::firstOrFail();
        $otherVenue = Venue::create(['name' => 'Other Floor', 'slug' => 'other-floor', 'timezone' => 'Europe/London']);
        $otherArea = DiningArea::create([
            'venue_id' => $otherVenue->id,
            'name' => 'Other Area',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $otherTable = RestaurantTable::create([
            'venue_id' => $otherVenue->id,
            'dining_area_id' => $otherArea->id,
            'name' => 'X1',
            'min_covers' => 1,
            'max_covers' => 2,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get('/admin/tables/'.$otherTable->id.'/edit')
            ->assertNotFound();

        $this->actingAs($user)
            ->patch('/admin/tables/'.$otherTable->id.'/toggle')
            ->assertNotFound();

        $this->actingAs($user)
            ->delete('/admin/tables/'.$otherTable->id)
            ->assertNotFound();

        $this->actingAs($user)
            ->get('/admin/areas/'.$otherArea->id.'/edit')
            ->assertNotFound();
    }

    public function test_one_venue_cannot_attach_table_to_another_venues_area(): void
    {
        $this->seed();
        $otherVenue = Venue::create(['name' => 'Other Area Venue', 'slug' => 'other-area-venue', 'timezone' => 'Europe/London']);
        $otherArea = DiningArea::create([
            'venue_id' => $otherVenue->id,
            'name' => 'Other Private Room',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->actingAs(User::first())
            ->post('/admin/tables', [
                'dining_area_id' => $otherArea->id,
                'name' => 'Bad Table',
                'min_covers' => 1,
                'max_covers' => 2,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('dining_area_id');
    }

    public function test_table_can_be_deactivated_and_is_hidden_from_booking_assignment(): void
    {
        $this->seed();
        $venue = Venue::firstOrFail();
        $table = RestaurantTable::where('name', 'O2')->firstOrFail();
        $service = $venue->services()->where('name', 'Dinner')->firstOrFail();
        $date = Carbon::now('Europe/London')->next(Carbon::MONDAY)->toDateString();

        $this->actingAs(User::first())
            ->patch('/admin/tables/'.$table->id.'/toggle')
            ->assertRedirect();

        $this->assertFalse($table->fresh()->is_active);

        $this->actingAs(User::first())
            ->post('/admin/bookings', [
                'service_id' => $service->id,
                'party_size' => 2,
                'date' => $date,
                'time' => '18:00',
                'first_name' => 'Inactive',
                'last_name' => 'Table',
                'email' => 'inactive-table@example.test',
                'phone' => '07111 222333',
                'source' => 'phone',
                'status' => 'confirmed',
                'table_ids' => [$table->id],
            ])
            ->assertSessionHasErrors();
    }

    public function test_premium_floorplan_prompt_respects_plan_gate(): void
    {
        $this->seed();
        $venue = Venue::firstOrFail();

        $this->actingAs(User::first())
            ->get('/admin/areas')
            ->assertOk()
            ->assertSee('Upgrade for floorplan tools');

        TenantSubscription::where('venue_id', $venue->id)->update([
            'plan' => 'premium',
            'status' => 'active',
            'trial_ends_at' => null,
        ]);

        $this->actingAs(User::first())
            ->get('/admin/areas')
            ->assertOk()
            ->assertSee('Premium ready')
            ->assertDontSee('Upgrade for floorplan tools');
    }
}
