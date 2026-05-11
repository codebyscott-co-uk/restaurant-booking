<?php

namespace App\Services;

use App\Models\Closure;
use App\Models\OpeningHour;
use App\Models\RestaurantTable;
use App\Models\Service;
use App\Models\Venue;
use Illuminate\Support\Carbon;

class BookingAvailability
{
    public function slots(Venue $venue, Service $service, Carbon $date, int $partySize, bool $includePast = false)
    {
        $hours = OpeningHour::where('service_id', $service->id)
            ->where('day_of_week', $date->dayOfWeek)
            ->first();

        if (! $hours || $hours->is_closed || ! $hours->opens_at || ! $hours->closes_at) {
            return collect();
        }

        $start = Carbon::parse($date->toDateString().' '.$hours->opens_at, $venue->timezone);
        $lastStart = Carbon::parse($date->toDateString().' '.$hours->closes_at, $venue->timezone)
            ->subMinutes($service->default_duration_minutes);

        if ($lastStart->lessThan($start)) {
            return collect();
        }

        return collect()
            ->range(0, 48)
            ->map(fn ($step) => $start->copy()->addMinutes($step * $service->slot_interval_minutes))
            ->takeWhile(fn (Carbon $slot) => $slot->lessThanOrEqualTo($lastStart))
            ->filter(fn (Carbon $slot) => $includePast || $slot->isFuture() || $slot->isToday() === false)
            ->filter(fn (Carbon $slot) => ! $this->isClosed($venue, $service, $slot, $slot->copy()->addMinutes($service->default_duration_minutes)))
            ->filter(fn (Carbon $slot) => $this->availableTable(
                $venue,
                $slot,
                $slot->copy()->addMinutes($service->default_duration_minutes),
                $partySize,
                $service
            ))
            ->values();
    }

    public function availableTable(Venue $venue, Carbon $startsAt, Carbon $endsAt, int $partySize, ?Service $service = null): ?RestaurantTable
    {
        if ($this->isClosed($venue, $service, $startsAt, $endsAt)) {
            return null;
        }

        return RestaurantTable::query()
            ->where('venue_id', $venue->id)
            ->where('is_active', true)
            ->where('min_covers', '<=', $partySize)
            ->where('max_covers', '>=', $partySize)
            ->whereDoesntHave('bookings', function ($query) use ($startsAt, $endsAt) {
                $query->whereNotIn('status', ['cancelled', 'no_show'])
                    ->where('starts_at', '<', $endsAt)
                    ->where('ends_at', '>', $startsAt);
            })
            ->orderBy('max_covers')
            ->first();
    }

    public function isClosed(Venue $venue, ?Service $service, Carbon $startsAt, Carbon $endsAt): bool
    {
        return Closure::query()
            ->where('venue_id', $venue->id)
            ->when($service, function ($query) use ($service) {
                $query->where(function ($query) use ($service) {
                    $query->whereNull('service_id')
                        ->orWhere('service_id', $service->id);
                });
            })
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();
    }
}

