<?php

namespace App\Models;

use App\Models\Concerns\BelongsToVenue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSubscription extends Model
{
    use BelongsToVenue;

    protected $fillable = [
        'venue_id',
        'provider',
        'provider_customer_id',
        'provider_subscription_id',
        'plan',
        'status',
        'trial_ends_at',
        'current_period_ends_at',
        'cancel_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'current_period_ends_at' => 'datetime',
            'cancel_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }
}
