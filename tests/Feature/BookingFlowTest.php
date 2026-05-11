<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_create_a_booking(): void
    {
        $this->seed();

        $response = $this->post('/book', [
            'service_id' => 1,
            'party_size' => 2,
            'date' => now()->addDay()->toDateString(),
            'time' => '12:00',
            'first_name' => 'Grace',
            'last_name' => 'Taylor',
            'email' => 'grace@example.test',
            'phone' => '07111 222333',
            'special_requests' => 'Window seat if possible.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['email' => 'grace@example.test']);
        $this->assertDatabaseHas('bookings', ['party_size' => 2, 'status' => 'confirmed']);
    }

    public function test_public_booking_page_does_not_show_diary_link(): void
    {
        $this->seed();

        $this->get('/')
            ->assertOk()
            ->assertSee('Staff login')
            ->assertDontSee('Diary');
    }

    public function test_guest_is_redirected_from_admin_diary_to_staff_login(): void
    {
        $this->seed();

        $this->get('/admin/diary')
            ->assertRedirect('/staff/login');
    }

    public function test_staff_can_log_in_and_view_admin_diary(): void
    {
        $this->seed();

        $this->post('/staff/login', [
            'email' => 'hello@codebyscott.co.uk',
            'password' => 'Letmein.123@',
        ])->assertRedirect('/admin/diary');

        $this->get('/admin/diary')
            ->assertOk()
            ->assertSee('Admin diary')
            ->assertSee('Log out');
    }

    public function test_authenticated_staff_can_view_admin_diary(): void
    {
        $this->seed();

        $this->actingAs(User::first())
            ->get('/admin/diary')
            ->assertOk()
            ->assertSee('Admin diary');
    }
}
