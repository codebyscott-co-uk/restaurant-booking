<?php

namespace Tests\Feature;

use App\Models\DiningArea;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->actingAs(User::first())
            ->delete('/admin/tables/'.$table->id)
            ->assertRedirect();

        $this->assertDatabaseHas('restaurant_tables', [
            'id' => $table->id,
            'name' => 'T1',
        ]);
    }
}

