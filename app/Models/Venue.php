<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Venue extends Model
{
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
    ];

    protected function casts(): array
    {
        return [
            'allow_joined_tables' => 'boolean',
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
}
