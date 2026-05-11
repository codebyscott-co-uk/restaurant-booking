<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_staff_only_see_their_own_venue_settings(): void
    {
        $this->seed();
        $secondVenue = Venue::create([
            'name' => 'Second Bistro',
            'slug' => 'second-bistro',
            'contact_email' => 'second@example.test',
        ]);
        $secondUser = User::create([
            'venue_id' => $secondVenue->id,
            'name' => 'Second Owner',
            'email' => 'second-owner@example.test',
            'password' => 'password',
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->actingAs($secondUser)
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('Second Bistro')
            ->assertDontSee('The Demo Table');
    }

    public function test_admin_staff_cannot_manage_another_venue_service(): void
    {
        $this->seed();
        $secondVenue = Venue::create([
            'name' => 'Second Bistro',
            'slug' => 'second-bistro',
        ]);
        $secondUser = User::create([
            'venue_id' => $secondVenue->id,
            'name' => 'Second Owner',
            'email' => 'second-owner@example.test',
            'password' => 'password',
            'role' => 'owner',
            'is_active' => true,
        ]);
        $firstVenueService = Service::where('name', 'Lunch')->firstOrFail();

        $this->actingAs($secondUser)
            ->get('/admin/services/'.$firstVenueService->id.'/edit')
            ->assertNotFound();
    }

    public function test_public_tenant_booking_page_and_api_use_venue_slug(): void
    {
        $this->seed();

        $this->get('/r/the-demo-table')
            ->assertOk()
            ->assertSee('Reserve your table at The Demo Table');

        $this->getJson('/api/v1/the-demo-table/venue')
            ->assertOk()
            ->assertJsonPath('data.slug', 'the-demo-table');

        $this->get('/r/the-demo-table/widget/bookings')
            ->assertOk()
            ->assertSee('The Demo Table');
    }
}
