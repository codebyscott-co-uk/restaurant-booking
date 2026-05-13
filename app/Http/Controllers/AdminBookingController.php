<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmationMail;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\RestaurantTable;
use App\Models\Service;
use App\Models\Venue;
use App\Services\BookingAvailability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminBookingController extends Controller
{
    public function create(Request $request, BookingAvailability $availability): View
    {
        $venue = $this->currentVenue($request)->load(['services', 'diningAreas.tables']);
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
            'booking' => new Booking(['status' => 'confirmed', 'source' => 'phone']),
            'availableTables' => $service ? $this->availableTablesForRequest($venue, $service, $date, $request->query('time'), $partySize, $availability) : collect(),
            'canUseCrm' => $venue->canUseFeature('customer_crm'),
        ]);
    }

    public function store(Request $request, BookingAvailability $availability): RedirectResponse
    {
        $venue = $this->currentVenue($request);

        $validated = $request->validate([
            'service_id' => ['required', Rule::exists('services', 'id')->where('venue_id', $venue->id)],
            'party_size' => ['required', 'integer', 'min:1', 'max:99'],
            'date' => ['required', 'date'],
            'time' => ['required', 'date_format:H:i'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'source' => ['required', Rule::in(['phone', 'walk_in', 'staff', 'web'])],
            'status' => ['required', Rule::in(Booking::STATUSES)],
            'special_requests' => ['nullable', 'string', 'max:2000'],
            'internal_notes' => ['nullable', 'string', 'max:2000'],
            'customer_notes' => ['nullable', 'string', 'max:2000'],
            'table_ids' => ['nullable', 'array'],
            'table_ids.*' => ['integer', Rule::exists('restaurant_tables', 'id')->where('venue_id', $venue->id)],
        ]);

        $service = Service::where('venue_id', $venue->id)->findOrFail($validated['service_id']);
        $startsAt = Carbon::parse($validated['date'].' '.$validated['time'], $venue->timezone);
        $endsAt = $startsAt->copy()->addMinutes($service->default_duration_minutes);
        $tables = $this->selectedOrAvailableTables($venue, $availability, $validated, $service, $startsAt, $endsAt);

        if ($tables->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['time' => 'No table is available for that date, time and party size.']);
        }

        $customer = $this->findOrPersistCustomer($venue, $validated);

        $booking = Booking::create([
            'venue_id' => $venue->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'booking_reference' => $this->reference(),
            'customer_manage_token' => Str::random(48),
            'party_size' => $validated['party_size'],
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => $validated['status'],
            'source' => $validated['source'],
            'special_requests' => $validated['special_requests'] ?? null,
            'internal_notes' => $validated['internal_notes'] ?? null,
            'confirmed_at' => in_array($validated['status'], ['confirmed', 'seated', 'completed'], true) ? now() : null,
        ]);

        $booking->tables()->attach($tables->pluck('id'));

        if (! str_ends_with($customer->email, '@local.test')) {
            Mail::to($customer->email)->send(new BookingConfirmationMail($booking));
        }

        return redirect()
            ->route('admin.diary', ['date' => $startsAt->toDateString()])
            ->with('status', 'Booking created.');
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $this->ensureVenue($booking, $this->currentVenue($request));

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

    public function edit(Request $request, Booking $booking, BookingAvailability $availability): View
    {
        $venue = $this->currentVenue($request)->load(['services', 'diningAreas.tables']);
        $this->ensureVenue($booking, $venue);
        $booking->load(['customer', 'service', 'tables.diningArea']);

        return view('admin.bookings.edit', [
            'venue' => $venue,
            'booking' => $booking,
            'services' => $venue->services()->where('is_active', true)->orderBy('starts_at')->get(),
            'selectedService' => $booking->service,
            'selectedDate' => $booking->starts_at->copy(),
            'partySize' => $booking->party_size,
            'slots' => $availability->slots($venue, $booking->service, $booking->starts_at->copy(), $booking->party_size, true, $booking->id),
            'availableTables' => $this->availableTablesForRequest($venue, $booking->service, $booking->starts_at->copy(), $booking->starts_at->format('H:i'), $booking->party_size, $availability, $booking->id),
            'canUseCrm' => $venue->canUseFeature('customer_crm'),
        ]);
    }

    public function update(Request $request, Booking $booking, BookingAvailability $availability): RedirectResponse
    {
        $venue = $this->currentVenue($request);
        $this->ensureVenue($booking, $venue);

        $validated = $request->validate([
            'service_id' => ['required', Rule::exists('services', 'id')->where('venue_id', $venue->id)],
            'party_size' => ['required', 'integer', 'min:1', 'max:99'],
            'date' => ['required', 'date'],
            'time' => ['required', 'date_format:H:i'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'source' => ['required', Rule::in(['phone', 'walk_in', 'staff', 'web'])],
            'status' => ['required', Rule::in(Booking::STATUSES)],
            'special_requests' => ['nullable', 'string', 'max:2000'],
            'internal_notes' => ['nullable', 'string', 'max:2000'],
            'customer_notes' => ['nullable', 'string', 'max:2000'],
            'table_ids' => ['nullable', 'array'],
            'table_ids.*' => ['integer', Rule::exists('restaurant_tables', 'id')->where('venue_id', $venue->id)],
        ]);

        $service = Service::where('venue_id', $venue->id)->findOrFail($validated['service_id']);
        $startsAt = Carbon::parse($validated['date'].' '.$validated['time'], $venue->timezone);
        $endsAt = $startsAt->copy()->addMinutes($service->default_duration_minutes);
        $tables = $this->selectedOrAvailableTables($venue, $availability, $validated, $service, $startsAt, $endsAt, $booking->id);

        if ($tables->isEmpty()) {
            return back()->withInput()->withErrors(['table_ids' => 'No suitable table is available for this booking.']);
        }

        $customer = $this->findOrPersistCustomer($venue, $validated, $booking->customer);

        $booking->update([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'party_size' => $validated['party_size'],
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => $validated['status'],
            'source' => $validated['source'],
            'special_requests' => $validated['special_requests'] ?? null,
            'internal_notes' => $validated['internal_notes'] ?? null,
            'confirmed_at' => in_array($validated['status'], ['confirmed', 'seated', 'completed'], true)
                ? ($booking->confirmed_at ?: now())
                : $booking->confirmed_at,
            'cancelled_at' => $validated['status'] === 'cancelled' ? now() : null,
        ]);

        $booking->tables()->sync($tables->pluck('id'));

        return redirect()
            ->route('admin.diary', ['date' => $startsAt->toDateString()])
            ->with('status', 'Booking updated.');
    }

    public function updateNotes(Request $request, Booking $booking): RedirectResponse
    {
        $this->ensureVenue($booking, $this->currentVenue($request));

        $validated = $request->validate([
            'internal_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $booking->update($validated);

        return back()->with('status', 'Booking notes updated.');
    }

    private function selectedOrAvailableTables(Venue $venue, BookingAvailability $availability, array $validated, Service $service, Carbon $startsAt, Carbon $endsAt, ?int $excludeBookingId = null)
    {
        $partySize = (int) $validated['party_size'];
        $selectedIds = collect($validated['table_ids'] ?? [])->filter()->map(fn ($id) => (int) $id)->values();

        if ($availability->isClosed($venue, $service, $startsAt, $endsAt)
            || ! $availability->isWithinOpeningHours($venue, $service, $startsAt, $endsAt)
            || ! $availability->isPartySizeAllowed($venue, $service, $partySize)
            || ! $availability->isWithinBookingWindow($venue, $startsAt, true)
            || $availability->exceedsSlotCapacity($venue, $startsAt, $endsAt, $partySize, $excludeBookingId)) {
            return collect();
        }

        if ($selectedIds->isEmpty()) {
            return $availability->availableTables($venue, $startsAt, $endsAt, $partySize, $service, $excludeBookingId);
        }

        $tables = RestaurantTable::with('diningArea')
            ->where('venue_id', $venue->id)
            ->where('is_active', true)
            ->whereHas('diningArea', fn ($query) => $query->where('is_active', true))
            ->whereIn('id', $selectedIds)
            ->get();

        if ($tables->count() !== $selectedIds->count()) {
            return collect();
        }

        $hasConflict = RestaurantTable::whereIn('id', $selectedIds)
            ->whereHas('bookings', function ($query) use ($startsAt, $endsAt, $excludeBookingId) {
                $query->whereNotIn('status', ['cancelled', 'no_show'])
                    ->when($excludeBookingId, fn ($query) => $query->whereKeyNot($excludeBookingId))
                    ->where('starts_at', '<', $endsAt)
                    ->where('ends_at', '>', $startsAt);
            })
            ->exists();

        if ($hasConflict || $tables->sum('max_covers') < $partySize) {
            return collect();
        }

        return $tables;
    }

    private function availableTablesForRequest(Venue $venue, ?Service $service, Carbon $date, ?string $time, int $partySize, BookingAvailability $availability, ?int $excludeBookingId = null)
    {
        if (! $service || ! $time) {
            return collect();
        }

        $startsAt = Carbon::parse($date->toDateString().' '.$time, $venue->timezone);
        $endsAt = $startsAt->copy()->addMinutes($service->default_duration_minutes);

        return $availability->availableTables($venue, $startsAt, $endsAt, $partySize, $service, $excludeBookingId);
    }

    private function reference(): string
    {
        do {
            $reference = 'CBR'.Str::upper(Str::random(6));
        } while (Booking::where('booking_reference', $reference)->exists());

        return $reference;
    }

    private function findOrPersistCustomer(Venue $venue, array $validated, ?Customer $currentCustomer = null): Customer
    {
        $email = $validated['email'] ?: null;
        $phone = $validated['phone'];
        $canUseCrm = $venue->canUseFeature('customer_crm');

        $matchedCustomer = Customer::query()
            ->where('venue_id', $venue->id)
            ->where(function ($query) use ($email, $phone) {
                $query->when($email, fn ($query) => $query->where('email', $email))
                    ->orWhere('phone', $phone);
            })
            ->orderByDesc('updated_at')
            ->first();

        $customer = $matchedCustomer ?: $currentCustomer ?: new Customer(['venue_id' => $venue->id]);

        $customer->fill([
            'venue_id' => $venue->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $email ?: ($currentCustomer?->email ?: 'guest+'.Str::lower(Str::random(8)).'@local.test'),
            'phone' => $phone,
        ]);

        if ($canUseCrm && array_key_exists('customer_notes', $validated)) {
            $customer->notes = $validated['customer_notes'];
        }

        $customer->save();

        return $customer;
    }
}
