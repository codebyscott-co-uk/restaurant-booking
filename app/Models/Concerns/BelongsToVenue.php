<?php

namespace App\Models\Concerns;

use App\Models\Venue;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToVenue
{
    public function scopeForVenue(Builder $query, Venue|int $venue): Builder
    {
        $venueId = $venue instanceof Venue ? $venue->id : $venue;

        return $query->where($query->qualifyColumn('venue_id'), $venueId);
    }

    public function belongsToVenue(Venue|int $venue): bool
    {
        $venueId = $venue instanceof Venue ? $venue->id : $venue;

        return (int) $this->getAttribute('venue_id') === (int) $venueId;
    }
}
