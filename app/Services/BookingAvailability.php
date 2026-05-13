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
    public function slots(Venue $venue, Service $service, Carbon $date, int $partySize, bool $includePast = false, ?int $excludeBookingId = null)
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
            ->filter(fn (Carbon $slot) => $includePast || $this->isWithinBookingWindow($venue, $slot))
            ->filter(fn (Carbon $slot) => ! $this->exceedsSlotCapacity($venue, $slot, $slot->copy()->addMinutes($service->default_duration_minutes), $partySize, $excludeBookingId))
            ->filter(fn (Carbon $slot) => ! $this->isClosed($venue, $service, $slot, $slot->copy()->addMinutes($service->default_duration_minutes)))
            ->filter(fn (Carbon $slot) => $this->availableTables(
                $venue,
                $slot,
                $slot->copy()->addMinutes($service->default_duration_minutes),
                $partySize,
                $service,
                $excludeBookingId
            ))
            ->values();
    }

    public function availableTable(Venue $venue, Carbon $startsAt, Carbon $endsAt, int $partySize, ?Service $service = null): ?RestaurantTable
    {
        return $this->availableTables($venue, $startsAt, $endsAt, $partySize, $service)->first();
    }

    public function availableTables(Venue $venue, Carbon $startsAt, Carbon $endsAt, int $partySize, ?Service $service = null, ?int $excludeBookingId = null)
    {
        if ($this->isClosed($venue, $service, $startsAt, $endsAt)) {
            return collect();
        }

        if ($service && ! $this->isWithinOpeningHours($venue, $service, $startsAt, $endsAt)) {
            return collect();
        }

        if (! $this->isPartySizeAllowed($venue, $service, $partySize)) {
            return collect();
        }

        if (! $this->isWithinBookingWindow($venue, $startsAt, true)) {
            return collect();
        }

        if ($this->exceedsSlotCapacity($venue, $startsAt, $endsAt, $partySize, $excludeBookingId)) {
            return collect();
        }

        $tables = RestaurantTable::query()
            ->where('venue_id', $venue->id)
            ->where('is_active', true)
            ->whereHas('diningArea', fn ($query) => $query->where('is_active', true))
            ->whereDoesntHave('bookings', function ($query) use ($startsAt, $endsAt, $excludeBookingId) {
                $query->whereNotIn('status', ['cancelled', 'no_show'])
                    ->when($excludeBookingId, fn ($query) => $query->whereKeyNot($excludeBookingId))
                    ->where('starts_at', '<', $endsAt)
                    ->where('ends_at', '>', $startsAt);
            })
            ->orderBy('max_covers')
            ->get();

        $single = $tables
            ->where('min_covers', '<=', $partySize)
            ->where('max_covers', '>=', $partySize)
            ->sortBy('max_covers')
            ->first();

        if ($single) {
            return collect([$single]);
        }

        if (! $venue->allow_joined_tables) {
            return collect();
        }

        $joined = collect();
        $capacity = 0;

        foreach ($tables->where('is_joinable', true)->sortBy('max_covers') as $table) {
            $joined->push($table);
            $capacity += $table->max_covers;

            if ($capacity >= $partySize) {
                return $joined;
            }
        }

        return collect();
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

    public function isPartySizeAllowed(Venue $venue, ?Service $service, int $partySize): bool
    {
        if ($partySize > $venue->maximum_party_size) {
            return false;
        }

        if ($service && ($partySize < $service->min_covers || $partySize > $service->max_covers)) {
            return false;
        }

        return true;
    }

    public function isWithinOpeningHours(Venue $venue, Service $service, Carbon $startsAt, Carbon $endsAt): bool
    {
        $hours = OpeningHour::where('service_id', $service->id)
            ->where('day_of_week', $startsAt->dayOfWeek)
            ->first();

        if (! $hours || $hours->is_closed || ! $hours->opens_at || ! $hours->closes_at) {
            return false;
        }

        $opensAt = Carbon::parse($startsAt->toDateString().' '.$hours->opens_at, $venue->timezone);
        $closesAt = Carbon::parse($startsAt->toDateString().' '.$hours->closes_at, $venue->timezone);

        return $startsAt->greaterThanOrEqualTo($opensAt) && $endsAt->lessThanOrEqualTo($closesAt);
    }

    public function isWithinBookingWindow(Venue $venue, Carbon $startsAt, bool $allowPastForStaff = false): bool
    {
        if (! $allowPastForStaff && $startsAt->lessThan(now($venue->timezone)->addMinutes($venue->minimum_lead_time_minutes))) {
            return false;
        }

        return $startsAt->lessThanOrEqualTo(now($venue->timezone)->addDays($venue->maximum_advance_booking_days));
    }

    public function exceedsSlotCapacity(Venue $venue, Carbon $startsAt, Carbon $endsAt, int $partySize, ?int $excludeBookingId = null): bool
    {
        if (! $venue->maximum_covers_per_slot) {
            return false;
        }

        $existingCovers = $venue->bookings()
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->when($excludeBookingId, fn ($query) => $query->whereKeyNot($excludeBookingId))
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->sum('party_size');

        return ($existingCovers + $partySize) > $venue->maximum_covers_per_slot;
    }
}
