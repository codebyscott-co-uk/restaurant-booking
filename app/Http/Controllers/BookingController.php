<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Closure;
use App\Models\Customer;
use App\Models\OpeningHour;
use App\Models\RestaurantTable;
use App\Models\Service;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(Request $request): View
    {
        $venue = Venue::with('services')->firstOrFail();
        $date = Carbon::parse($request->query('date', today($venue->timezone)->toDateString()), $venue->timezone);
        $partySize = max(1, min(12, (int) $request->query('party_size', 2)));
        $service = Service::where('venue_id', $venue->id)
            ->where('is_active', true)
            ->find($request->query('service_id')) ?: $venue->services()->where('is_active', true)->first();

        $slots = $service ? $this->availableSlots($venue, $service, $date, $partySize) : collect();

        return view('bookings.create', [
            'venue' => $venue,
            'services' => $venue->services()->where('is_active', true)->orderBy('starts_at')->get(),
            'selectedService' => $service,
            'selectedDate' => $date,
            'partySize' => $partySize,
            'slots' => $slots,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $venue = Venue::firstOrFail();

        $validated = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'party_size' => ['required', 'integer', 'min:1', 'max:12'],
            'date' => ['required', 'date'],
            'time' => ['required', 'date_format:H:i'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'special_requests' => ['nullable', 'string', 'max:2000'],
            'marketing_opt_in' => ['nullable', 'boolean'],
        ]);

        $service = Service::where('venue_id', $venue->id)->findOrFail($validated['service_id']);
        $startsAt = Carbon::parse($validated['date'].' '.$validated['time'], $venue->timezone);
        $endsAt = $startsAt->copy()->addMinutes($service->default_duration_minutes);

        $table = $this->availableTable($venue, $startsAt, $endsAt, (int) $validated['party_size']);

        if (! $table) {
            return back()
                ->withInput()
                ->withErrors(['time' => 'That time has just been taken. Please choose another available slot.']);
        }

        $customer = Customer::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'marketing_opt_in' => (bool) ($validated['marketing_opt_in'] ?? false),
        ]);

        $booking = Booking::create([
            'venue_id' => $venue->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'booking_reference' => $this->reference(),
            'party_size' => $validated['party_size'],
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => 'confirmed',
            'source' => 'web',
            'special_requests' => $validated['special_requests'] ?? null,
            'confirmed_at' => now(),
        ]);

        $booking->tables()->attach($table);

        return redirect()->route('bookings.show', $booking)->with('status', 'Your table is booked.');
    }

    public function show(Booking $booking): View
    {
        return view('bookings.show', ['booking' => $booking->load('venue', 'customer', 'service', 'tables.diningArea')]);
    }

    private function availableSlots(Venue $venue, Service $service, Carbon $date, int $partySize)
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
            ->filter(fn (Carbon $slot) => $slot->isFuture() || $slot->isToday() === false)
            ->filter(fn (Carbon $slot) => ! $this->isClosed($venue, $service, $slot, $slot->copy()->addMinutes($service->default_duration_minutes)))
            ->filter(fn (Carbon $slot) => $this->availableTable(
                $venue,
                $slot,
                $slot->copy()->addMinutes($service->default_duration_minutes),
                $partySize
            ))
            ->values();
    }

    private function availableTable(Venue $venue, Carbon $startsAt, Carbon $endsAt, int $partySize): ?RestaurantTable
    {
        if ($this->isClosed($venue, null, $startsAt, $endsAt)) {
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

    private function isClosed(Venue $venue, ?Service $service, Carbon $startsAt, Carbon $endsAt): bool
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

    private function reference(): string
    {
        do {
            $reference = 'CBR'.Str::upper(Str::random(6));
        } while (Booking::where('booking_reference', $reference)->exists());

        return $reference;
    }
}
