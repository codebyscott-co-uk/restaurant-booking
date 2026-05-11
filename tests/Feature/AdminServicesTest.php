<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_manage_services(): void
    {
        $this->seed();

        $this->get('/admin/services')->assertRedirect('/staff/login');
    }

    public function test_staff_can_create_a_service(): void
    {
        $this->seed();

        $this->actingAs(User::first())
            ->post('/admin/services', [
                'name' => 'Brunch',
                'starts_at' => '10:00',
                'ends_at' => '13:30',
                'slot_interval_minutes' => 30,
                'default_duration_minutes' => 90,
                'min_covers' => 1,
                'max_covers' => 6,
                'requires_deposit' => '1',
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/services');

        $this->assertDatabaseHas('services', [
            'name' => 'Brunch',
            'requires_deposit' => true,
            'is_active' => true,
        ]);
    }

    public function test_staff_can_update_a_service(): void
    {
        $this->seed();
        $service = Service::where('name', 'Lunch')->firstOrFail();

        $this->actingAs(User::first())
            ->put('/admin/services/'.$service->id, [
                'name' => 'Early Lunch',
                'starts_at' => '11:30',
                'ends_at' => '15:00',
                'slot_interval_minutes' => 15,
                'default_duration_minutes' => 75,
                'min_covers' => 1,
                'max_covers' => 4,
                'requires_deposit' => '',
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/services');

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Early Lunch',
            'slot_interval_minutes' => 15,
            'default_duration_minutes' => 75,
            'max_covers' => 4,
        ]);
    }

    public function test_service_with_bookings_cannot_be_deleted(): void
    {
        $this->seed();
        $service = Service::where('name', 'Lunch')->firstOrFail();

        $this->actingAs(User::first())
            ->delete('/admin/services/'.$service->id)
            ->assertRedirect();

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Lunch',
        ]);
    }
}

