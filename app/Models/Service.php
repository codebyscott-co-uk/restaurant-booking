<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'venue_id',
        'name',
        'starts_at',
        'ends_at',
        'slot_interval_minutes',
        'default_duration_minutes',
        'min_covers',
        'max_covers',
        'requires_deposit',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'requires_deposit' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
