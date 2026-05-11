<?php

namespace Tests\Feature;

use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_widget_page_renders(): void
    {
        $this->seed();
        Venue::firstOrFail()->update([
            'widget_title' => 'Reserve online',
            'widget_intro' => 'Book directly from our website.',
            'widget_button_text' => 'Reserve now',
        ]);

        $this->get('/widget/bookings')
            ->assertOk()
            ->assertSee('Reserve online')
            ->assertSee('Book directly from our website.')
            ->assertSee('/api/v1', false);
    }

    public function test_public_widget_script_renders_iframe_embed(): void
    {
        $this->seed();

        $this->get('/widget/embed.js')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/javascript')
            ->assertSee('/widget/bookings')
            ->assertSee('iframe');
    }

    public function test_disabled_public_widget_returns_not_found(): void
    {
        $this->seed();
        Venue::firstOrFail()->update(['widget_enabled' => false]);

        $this->get('/widget/bookings')->assertNotFound();
    }
}
