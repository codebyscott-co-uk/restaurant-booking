<?php

namespace App\Models;

use App\Models\Concerns\BelongsToVenue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Booking extends Model
{
    use BelongsToVenue;

    public const STATUSES = ['pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'];

    protected $fillable = [
        'venue_id',
        'customer_id',
        'service_id',
        'booking_reference',
        'customer_manage_token',
        'party_size',
        'starts_at',
        'ends_at',
        'status',
        'source',
        'special_requests',
        'internal_notes',
        'deposit_amount',
        'deposit_status',
        'confirmed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'deposit_amount' => 'decimal:2',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'booking_reference';
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function tables(): BelongsToMany
    {
        return $this->belongsToMany(RestaurantTable::class)->withTimestamps();
    }

    public function canCustomerManage(): bool
    {
        return ! in_array($this->status, ['cancelled', 'completed', 'no_show', 'seated'], true)
            && $this->customer_manage_token
            && $this->starts_at->isFuture();
    }

    public function canCustomerCancel(): bool
    {
        if (! $this->canCustomerManage()) {
            return false;
        }

        $noticeHours = $this->venue?->cancellation_notice_hours ?? 24;

        return $this->starts_at->greaterThanOrEqualTo(now($this->venue->timezone)->addHours($noticeHours));
    }
}
