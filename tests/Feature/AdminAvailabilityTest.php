<?php

namespace Tests\Feature;

use App\Models\Closure;
use App\Models\OpeningHour;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_manage_availability(): void
    {
        $this->seed();

        $this->get('/admin/availability')->assertRedirect('/staff/login');
    }

    public function test_staff_can_update_opening_hours(): void
    {
        $this->seed();
        $hours = OpeningHour::all()->groupBy('service_id')->map(function ($serviceHours) {
            return $serviceHours->mapWithKeys(fn (OpeningHour $hour) => [
                $hour->day_of_week => [
                    'id' => $hour->id,
                    'opens_at' => '09:00',
                    'closes_at' => '14:00',
                    'is_closed' => $hour->day_of_week === 0 ? '1' : '',
                ],
            ])->all();
        })->all();

        $this->actingAs(User::first())
            ->put('/admin/availability/hours', ['hours' => $hours])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/availability');

        $this->assertDatabaseHas('opening_hours', [
            'day_of_week' => 1,
            'opens_at' => '09:00',
            'closes_at' => '14:00',
            'is_closed' => false,
        ]);

        $this->assertDatabaseHas('opening_hours', [
            'day_of_week' => 0,
            'opens_at' => null,
            'closes_at' => null,
            'is_closed' => true,
        ]);
    }

    public function test_staff_can_add_and_remove_closure(): void
    {
        $this->seed();
        $service = Service::firstOrFail();

        $this->actingAs(User::first())
            ->post('/admin/availability/closures', [
                'service_id' => $service->id,
                'starts_at' => now()->addDay()->format('Y-m-d\T10:00'),
                'ends_at' => now()->addDay()->format('Y-m-d\T12:00'),
                'reason' => 'Private event',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/availability');

        $closure = Closure::where('reason', 'Private event')->firstOrFail();

        $this->actingAs(User::first())
            ->delete('/admin/availability/closures/'.$closure->id)
            ->assertRedirect('/admin/availability');

        $this->assertDatabaseMissing('closures', ['id' => $closure->id]);
    }

    public function test_closed_day_removes_public_booking_slots(): void
    {
        $this->seed();
        $service = Service::where('name', 'Lunch')->firstOrFail();
        $nextMonday = now()->next('Monday')->toDateString();

        OpeningHour::where('service_id', $service->id)
            ->where('day_of_week', 1)
            ->update(['is_closed' => true, 'opens_at' => null, 'closes_at' => null]);

        $this->get('/?date='.$nextMonday.'&party_size=2&service_id='.$service->id)
            ->assertOk()
            ->assertSee('No available times');
    }
}

