<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmationMail;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Venue;
use App\Services\BookingAvailability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class CustomerBookingController extends Controller
{
    public function lookup(Venue $venue = null): View
    {
        return view('bookings.lookup', [
            'venue' => $venue ?: Venue::firstOrFail(),
        ]);
    }

    public function find(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'booking_reference' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $booking = Booking::with('customer')
            ->where('booking_reference', strtoupper($validated['booking_reference']))
            ->whereHas('customer', fn ($query) => $query->whereRaw('lower(email) = ?', [strtolower($validated['email'])]))
            ->first();

        if (! $booking || ! $booking->customer_manage_token) {
            return back()
                ->withInput()
                ->withErrors(['booking_reference' => 'We could not find a booking matching those details.']);
        }

        return redirect()->route('bookings.manage.show', [
            'booking' => $booking,
            'token' => $booking->customer_manage_token,
        ]);
    }

    public function show(Booking $booking, string $token): View
    {
        $booking = $this->authorisedBooking($booking, $token);

        return view('bookings.manage', [
            'booking' => $booking,
            'venue' => $booking->venue,
        ]);
    }

    public function edit(Request $request, Booking $booking, string $token): View
    {
        $booking = $this->authorisedBooking($booking, $token);
        $venue = $booking->venue;
        $date = Carbon::parse($request->query('date', $booking->starts_at->toDateString()), $venue->timezone);
        $partySize = max(1, min($venue->maximum_party_size, (int) $request->query('party_size', $booking->party_size)));
        $service = Service::where('venue_id', $venue->id)
            ->where('is_active', true)
            ->find($request->query('service_id', $booking->service_id)) ?: $booking->service;

        $slots = $booking->canCustomerCancel()
            ? app(BookingAvailability::class)->slots($venue, $service, $date, $partySize, false, $booking->id)
            : collect();

        return view('bookings.edit', [
            'booking' => $booking,
            'venue' => $venue,
            'services' => $venue->services()->where('is_active', true)->orderBy('starts_at')->get(),
            'selectedService' => $service,
            'selectedDate' => $date,
            'partySize' => $partySize,
            'slots' => $slots,
        ]);
    }

    public function update(Request $request, Booking $booking, string $token): RedirectResponse
    {
        $booking = $this->authorisedBooking($booking, $token);

        if (! $booking->canCustomerCancel()) {
            return back()
                ->withInput()
                ->withErrors(['booking' => 'This booking can no longer be changed online. Please contact the restaurant.']);
        }

        $venue = $booking->venue;

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
        ]);

        $service = Service::where('venue_id', $venue->id)->findOrFail($validated['service_id']);
        $startsAt = Carbon::parse($validated['date'].' '.$validated['time'], $venue->timezone);
        $endsAt = $startsAt->copy()->addMinutes($service->default_duration_minutes);

        if ($startsAt->lessThan(now($venue->timezone)->addHours($venue->cancellation_notice_hours))) {
            return back()
                ->withInput()
                ->withErrors(['time' => 'This booking is inside the change and cancellation window. Please contact the restaurant.']);
        }

        $tables = app(BookingAvailability::class)->availableTables(
            $venue,
            $startsAt,
            $endsAt,
            (int) $validated['party_size'],
            $service,
            $booking->id
        );

        if ($tables->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['time' => 'That time is no longer available. Please choose another slot.']);
        }

        $booking->customer->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        $booking->update([
            'service_id' => $service->id,
            'party_size' => $validated['party_size'],
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'special_requests' => $validated['special_requests'] ?? null,
            'status' => 'confirmed',
            'cancelled_at' => null,
        ]);

        $booking->tables()->sync($tables->pluck('id'));
        Mail::to($booking->customer->email)->send(new BookingConfirmationMail($booking->fresh()));

        return redirect()
            ->route('bookings.manage.show', ['booking' => $booking, 'token' => $token])
            ->with('status', 'Your booking has been updated.');
    }

    public function cancel(Booking $booking, string $token): RedirectResponse
    {
        $booking = $this->authorisedBooking($booking, $token);

        if (! $booking->canCustomerCancel()) {
            return back()->withErrors(['booking' => 'This booking can no longer be cancelled online. Please contact the restaurant.']);
        }

        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return redirect()
            ->route('bookings.manage.show', ['booking' => $booking, 'token' => $token])
            ->with('status', 'Your booking has been cancelled.');
    }

    private function authorisedBooking(Booking $booking, string $token): Booking
    {
        abort_unless(hash_equals((string) $booking->customer_manage_token, $token), 404);

        return $booking->load('venue', 'customer', 'service', 'tables.diningArea');
    }
}
