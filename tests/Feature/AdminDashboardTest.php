<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_dashboard_to_staff_login(): void
    {
        $this->seed();

        $this->get('/admin')
            ->assertRedirect('/staff/login');
    }

    public function test_staff_can_view_admin_dashboard(): void
    {
        $this->seed();

        $this->actingAs(User::first())
            ->get('/admin')
            ->assertOk()
            ->assertSee('Staff dashboard')
            ->assertSee('Today bookings')
            ->assertSee('Staff workspace')
            ->assertSee('Booking diary')
            ->assertSee('Quick actions')
            ->assertSee('Setup health');
    }
}
