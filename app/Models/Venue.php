<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Laravel\Cashier\Billable;

class Venue extends Model
{
    use Billable;

    protected $fillable = [
        'name',
        'slug',
        'contact_email',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'county',
        'postcode',
        'country',
        'website_url',
        'minimum_lead_time_minutes',
        'maximum_advance_booking_days',
        'maximum_party_size',
        'maximum_covers_per_slot',
        'allow_joined_tables',
        'cancellation_notice_hours',
        'timezone',
        'logo_path',
        'primary_colour',
        'accent_colour',
        'booking_terms',
        'cancellation_policy',
        'email_confirmation_content',
        'email_modification_content',
        'email_cancellation_content',
        'email_reminder_content',
        'email_staff_alert_content',
        'email_footer_content',
        'widget_enabled',
        'widget_title',
        'widget_intro',
        'widget_button_text',
        'stripe_id',
        'stripe_status',
        'pm_type',
        'pm_last_four',
        'stripe_price_id',
        'stripe_price_name',
        'stripe_current_period_start',
        'stripe_current_period_end',
        'trial_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'allow_joined_tables' => 'boolean',
            'widget_enabled' => 'boolean',
            'stripe_current_period_start' => 'datetime',
            'stripe_current_period_end' => 'datetime',
            'trial_ends_at' => 'datetime',
        ];
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    public function diningAreas(): HasMany
    {
        return $this->hasMany(DiningArea::class)->orderBy('sort_order');
    }

    public function tables(): HasMany
    {
        return $this->hasMany(RestaurantTable::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function openingHours(): HasMany
    {
        return $this->hasMany(OpeningHour::class);
    }

    public function closures(): HasMany
    {
        return $this->hasMany(Closure::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
