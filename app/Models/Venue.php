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
        'timezone',
        'logo_path',
        'primary_colour',
        'accent_colour',
        'booking_terms',
        'cancellation_policy',
    ];

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
