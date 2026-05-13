<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmationMail;
use App\Mail\NewBookingStaffAlertMail;
use App\Models\Booking;
use App\Models\Customer;
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

class BookingController extends Controller
{
    public function create(Request $request, ?Venue $venue = null): View
    {
        $venue = ($venue ?: Venue::query()->firstOrFail())->load('services');
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
            'bookingStoreRoute' => $request->route('venue')
                ? route('tenant.bookings.store', $venue)
                : route('bookings.store'),
        ]);
    }

    public function store(Request $request, ?Venue $venue = null): RedirectResponse
    {
        $venue = $venue ?: Venue::firstOrFail();

        $validated = $request->validate([
            'service_id' => ['required', Rule::exists('services', 'id')->where('venue_id', $venue->id)->where('is_active', true)],
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
            'status' => 'confirmed',
            'source' => 'web',
            'special_requests' => $validated['special_requests'] ?? null,
            'confirmed_at' => now(),
        ]);

        $booking->tables()->attach($tables->pluck('id'));
        Mail::to($customer->email)->send(new BookingConfirmationMail($booking));

        if ($venue->contact_email) {
            Mail::to($venue->contact_email)->send(new NewBookingStaffAlertMail($booking));
        }

        $showRoute = $request->route('venue') ? 'tenant.bookings.show' : 'bookings.show';
        $showParameters = [
            'booking' => $booking,
            'token' => $booking->customer_manage_token,
        ];

        if ($request->route('venue')) {
            $showParameters['venue'] = $venue;
        }

        return redirect()->route($showRoute, $showParameters)->with('status', 'Your table is booked.');
    }

    public function show(Request $request, Booking $booking): View
    {
        abort_unless(hash_equals((string) $booking->customer_manage_token, (string) $request->query('token')), 404);
        $this->ensurePublicVenue($request, $booking);

        return view('bookings.show', ['booking' => $booking->load('venue', 'customer', 'service', 'tables.diningArea')]);
    }

    public function tenantShow(Request $request, Venue $venue, Booking $booking): View
    {
        return $this->show($request, $booking);
    }

    protected function ensurePublicVenue(Request $request, Booking $booking): void
    {
        $venue = $request->route('venue');

        if ($venue instanceof Venue) {
            abort_unless((int) $booking->venue_id === (int) $venue->id, 404);
        }
    }

    private function reference(): string
    {
        do {
            $reference = 'CBR'.Str::upper(Str::random(6));
        } while (Booking::where('booking_reference', $reference)->exists());

        return $reference;
    }

    private function findOrPersistCustomer(Venue $venue, array $validated): Customer
    {
        $customer = Customer::query()
            ->where('venue_id', $venue->id)
            ->where(function ($query) use ($validated) {
                $query->where('email', $validated['email'])
                    ->orWhere('phone', $validated['phone']);
            })
            ->orderByDesc('updated_at')
            ->first() ?: new Customer(['venue_id' => $venue->id]);

        $customer->fill([
            'venue_id' => $venue->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'marketing_opt_in' => $customer->marketing_opt_in || (bool) ($validated['marketing_opt_in'] ?? false),
        ])->save();

        return $customer;
    }
}
