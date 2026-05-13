<?php

namespace App\Models;

use App\Models\Concerns\BelongsToVenue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use BelongsToVenue;

    protected $fillable = [
        'venue_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'notes',
        'marketing_opt_in',
        'is_vip',
        'allergies',
        'dietary_requirements',
        'preferences',
        'favourite_dining_area_id',
        'favourite_restaurant_table_id',
    ];

    protected function casts(): array
    {
        return [
            'marketing_opt_in' => 'boolean',
            'is_vip' => 'boolean',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function favouriteDiningArea(): BelongsTo
    {
        return $this->belongsTo(DiningArea::class, 'favourite_dining_area_id');
    }

    public function favouriteRestaurantTable(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'favourite_restaurant_table_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function getHasProfileNotesAttribute(): bool
    {
        return filled($this->notes)
            || filled($this->allergies)
            || filled($this->dietary_requirements)
            || filled($this->preferences);
    }
}
