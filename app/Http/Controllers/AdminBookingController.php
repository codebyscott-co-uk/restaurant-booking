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
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminBookingController extends Controller
{
    public function create(Request $request, BookingAvailability $availability): View
    {
        $venue = Venue::with('services')->firstOrFail();
        $date = Carbon::parse($request->query('date', today($venue->timezone)->toDateString()), $venue->timezone);
        $partySize = max(1, min(99, (int) $request->query('party_size', 2)));
        $service = Service::where('venue_id', $venue->id)
            ->where('is_active', true)
            ->find($request->query('service_id')) ?: $venue->services()->where('is_active', true)->first();

        return view('admin.bookings.create', [
            'venue' => $venue,
            'services' => $venue->services()->where('is_active', true)->orderBy('starts_at')->get(),
            'selectedService' => $service,
            'selectedDate' => $date,
            'partySize' => $partySize,
            'slots' => $service ? $availability->slots($venue, $service, $date, $partySize, true) : collect(),
        ]);
    }

    public function store(Request $request, BookingAvailability $availability): RedirectResponse
    {
        $venue = Venue::firstOrFail();

        $validated = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'party_size' => ['required', 'integer', 'min:1', 'max:99'],
            'date' => ['required', 'date'],
            'time' => ['required', 'date_format:H:i'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'source' => ['required', Rule::in(['phone', 'walk_in', 'staff'])],
            'status' => ['required', Rule::in(Booking::STATUSES)],
            'special_requests' => ['nullable', 'string', 'max:2000'],
            'internal_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $service = Service::where('venue_id', $venue->id)->findOrFail($validated['service_id']);
        $startsAt = Carbon::parse($validated['date'].' '.$validated['time'], $venue->timezone);
        $endsAt = $startsAt->copy()->addMinutes($service->default_duration_minutes);
        $table = $availability->availableTable($venue, $startsAt, $endsAt, (int) $validated['party_size'], $service);

        if (! $table) {
            return back()
                ->withInput()
                ->withErrors(['time' => 'No table is available for that date, time and party size.']);
        }

        $customer = Customer::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'] ?: 'guest+'.Str::lower(Str::random(8)).'@local.test',
            'phone' => $validated['phone'],
            'notes' => $validated['internal_notes'] ?? null,
        ]);

        $booking = Booking::create([
            'venue_id' => $venue->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'booking_reference' => $this->reference(),
            'party_size' => $validated['party_size'],
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => $validated['status'],
            'source' => $validated['source'],
            'special_requests' => $validated['special_requests'] ?? null,
            'internal_notes' => $validated['internal_notes'] ?? null,
            'confirmed_at' => in_array($validated['status'], ['confirmed', 'seated', 'completed'], true) ? now() : null,
        ]);

        $booking->tables()->attach($table);

        return redirect()
            ->route('admin.diary', ['date' => $startsAt->toDateString()])
            ->with('status', 'Booking created.');
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(Booking::STATUSES)],
        ]);

        $booking->update([
            'status' => $validated['status'],
            'confirmed_at' => in_array($validated['status'], ['confirmed', 'seated', 'completed'], true)
                ? ($booking->confirmed_at ?: now())
                : $booking->confirmed_at,
            'cancelled_at' => $validated['status'] === 'cancelled' ? now() : null,
        ]);

        return redirect()
            ->route('admin.diary', ['date' => $booking->starts_at->toDateString()])
            ->with('status', 'Booking status updated.');
    }

    private function reference(): string
    {
        do {
            $reference = 'CBR'.Str::upper(Str::random(6));
        } while (Booking::where('booking_reference', $reference)->exists());

        return $reference;
    }
}
