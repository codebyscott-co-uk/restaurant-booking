<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmationMail;
use App\Mail\NewBookingStaffAlertMail;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_returns_venue_details(): void
    {
        $this->seed();

        $this->getJson('/api/v1/venue')
            ->assertOk()
            ->assertJsonPath('data.name', 'The Demo Table')
            ->assertJsonPath('data.maximum_party_size', 10)
            ->assertJsonPath('data.cancellation_notice_hours', 24);
    }

    public function test_api_returns_services(): void
    {
        $this->seed();

        $this->getJson('/api/v1/services')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Lunch');
    }

    public function test_api_returns_availability_slots(): void
    {
        $this->seed();
        $service = Service::where('name', 'Lunch')->firstOrFail();

        $this->getJson('/api/v1/availability?service_id='.$service->id.'&date='.now()->next('Monday')->toDateString().'&party_size=2')
            ->assertOk()
            ->assertJsonPath('data.service_id', $service->id)
            ->assertJsonCount(3, 'data.slots');
    }

    public function test_api_can_create_booking(): void
    {
        $this->seed();
        Mail::fake();
        $service = Service::where('name', 'Lunch')->firstOrFail();

        $this->postJson('/api/v1/bookings', [
            'service_id' => $service->id,
            'party_size' => 2,
            'date' => now()->next('Monday')->toDateString(),
            'time' => '12:00',
            'first_name' => 'Api',
            'last_name' => 'Guest',
            'email' => 'api@example.test',
            'phone' => '07111 123123',
            'special_requests' => 'API booking.',
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'confirmed')
            ->assertJsonPath('data.service', 'Lunch')
            ->assertJsonStructure(['data' => ['manage_url']]);

        $this->assertDatabaseHas('bookings', ['source' => 'api', 'party_size' => 2]);
        $this->assertNotNull(\App\Models\Booking::where('source', 'api')->firstOrFail()->customer_manage_token);
        Mail::assertSent(BookingConfirmationMail::class, fn (BookingConfirmationMail $mail) => $mail->hasTo('api@example.test'));
        Mail::assertSent(NewBookingStaffAlertMail::class, fn (NewBookingStaffAlertMail $mail) => $mail->hasTo('bookings@demo-restaurant.test'));
    }

    public function test_api_rejects_unavailable_booking(): void
    {
        $this->seed();
        $service = Service::where('name', 'Lunch')->firstOrFail();

        $this->postJson('/api/v1/bookings', [
            'service_id' => $service->id,
            'party_size' => 2,
            'date' => now()->next('Sunday')->toDateString(),
            'time' => '12:00',
            'first_name' => 'No',
            'last_name' => 'Table',
            'email' => 'no-table@example.test',
            'phone' => '07111 000000',
        ])->assertUnprocessable();
    }
}
