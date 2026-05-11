<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Venue;
use App\Services\BookingAvailability;
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
        $partySize = max(1, min($venue->maximum_party_size, (int) $request->query('party_size', 2)));
        $service = Service::where('venue_id', $venue->id)
            ->where('is_active', true)
            ->find($request->query('service_id')) ?: $venue->services()->where('is_active', true)->first();

        $slots = $service ? app(BookingAvailability::class)->slots($venue, $service, $date, $partySize) : collect();

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
            'party_size' => ['required', 'integer', 'min:1', 'max:'.$venue->maximum_party_size],
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

        $tables = app(BookingAvailability::class)->availableTables($venue, $startsAt, $endsAt, (int) $validated['party_size'], $service);

        if ($tables->isEmpty()) {
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

        $booking->tables()->attach($tables->pluck('id'));

        return redirect()->route('bookings.show', $booking)->with('status', 'Your table is booked.');
    }

    public function show(Booking $booking): View
    {
        return view('bookings.show', ['booking' => $booking->load('venue', 'customer', 'service', 'tables.diningArea')]);
    }

    private function reference(): string
    {
        do {
            $reference = 'CBR'.Str::upper(Str::random(6));
        } while (Booking::where('booking_reference', $reference)->exists());

        return $reference;
    }
}
