<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_update_business_settings(): void
    {
        $this->seed();

        $this->actingAs(User::first())
            ->put('/admin/settings', [
                'name' => 'Code by Scott Bistro',
                'contact_email' => 'hello@codebyscott.co.uk',
                'phone' => '01234 567890',
                'address_line_1' => '1 Test Street',
                'city' => 'Glasgow',
                'postcode' => 'G1 1AA',
                'country' => 'United Kingdom',
                'website_url' => 'https://codebyscott.co.uk',
                'minimum_lead_time_minutes' => 30,
                'maximum_advance_booking_days' => 90,
                'maximum_party_size' => 12,
                'maximum_covers_per_slot' => 40,
                'allow_joined_tables' => '1',
                'cancellation_notice_hours' => 12,
                'timezone' => 'Europe/London',
                'primary_colour' => '#123456',
                'accent_colour' => '#abcdef',
                'booking_terms' => 'Updated booking terms.',
                'cancellation_policy' => 'Updated cancellation policy.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('venues', [
            'name' => 'Code by Scott Bistro',
            'city' => 'Glasgow',
            'primary_colour' => '#123456',
            'maximum_party_size' => 12,
            'allow_joined_tables' => true,
            'cancellation_notice_hours' => 12,
        ]);
    }

    public function test_staff_can_create_update_and_delete_staff_users(): void
    {
        $this->seed();
        $admin = User::first();

        $this->actingAs($admin)
            ->post('/admin/staff', [
                'name' => 'Front Desk',
                'email' => 'frontdesk@example.test',
                'role' => 'host',
                'phone' => '07000 111222',
                'job_title' => 'Host',
                'is_active' => '1',
                'password' => 'Secretpass.123',
                'password_confirmation' => 'Secretpass.123',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/staff');

        $user = User::where('email', 'frontdesk@example.test')->firstOrFail();

        $this->actingAs($admin)
            ->put('/admin/staff/'.$user->id, [
                'name' => 'Front Desk Lead',
                'email' => 'frontdesk@example.test',
                'role' => 'manager',
                'phone' => '07000 111222',
                'job_title' => 'Front of House Lead',
                'is_active' => '1',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/staff');

        $this->assertDatabaseHas('users', [
            'email' => 'frontdesk@example.test',
            'name' => 'Front Desk Lead',
            'role' => 'manager',
        ]);

        $this->actingAs($admin)
            ->delete('/admin/staff/'.$user->id)
            ->assertRedirect('/admin/staff');

        $this->assertDatabaseMissing('users', ['email' => 'frontdesk@example.test']);
    }

    public function test_staff_cannot_delete_their_own_account(): void
    {
        $this->seed();
        $admin = User::first();

        $this->actingAs($admin)
            ->delete('/admin/staff/'.$admin->id)
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'hello@codebyscott.co.uk']);
    }

    public function test_staff_pages_include_modal_confirmations_for_destructive_actions(): void
    {
        $this->seed();

        $this->actingAs(User::first())
            ->get('/admin/staff')
            ->assertOk()
            ->assertSee('data-confirm-modal', false)
            ->assertSee('data-confirm="Delete this staff user?"', false);
    }

    public function test_guest_cannot_view_settings_or_staff_pages(): void
    {
        $this->seed();

        $this->get('/admin/settings')->assertRedirect('/staff/login');
        $this->get('/admin/staff')->assertRedirect('/staff/login');
    }
}
