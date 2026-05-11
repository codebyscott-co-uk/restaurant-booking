<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\BookingConfirmationMail;
use App\Mail\NewBookingStaffAlertMail;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Venue;
use App\Services\BookingAvailability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class BookingApiController extends Controller
{
    public function venue(?Venue $venue = null): JsonResponse
    {
        $venue = $venue ?: Venue::firstOrFail();

        return response()->json([
            'data' => [
                'name' => $venue->name,
                'slug' => $venue->slug,
                'phone' => $venue->phone,
                'contact_email' => $venue->contact_email,
                'timezone' => $venue->timezone,
                'logo_url' => $venue->logo_url,
                'primary_colour' => $venue->primary_colour,
                'accent_colour' => $venue->accent_colour,
                'booking_terms' => $venue->booking_terms,
                'cancellation_policy' => $venue->cancellation_policy,
                'cancellation_notice_hours' => $venue->cancellation_notice_hours,
                'maximum_party_size' => $venue->maximum_party_size,
                'maximum_advance_booking_days' => $venue->maximum_advance_booking_days,
            ],
        ]);
    }

    public function services(?Venue $venue = null): JsonResponse
    {
        $venue = $venue ?: Venue::firstOrFail();

        return response()->json([
            'data' => $venue->services()
                ->where('is_active', true)
                ->orderBy('starts_at')
                ->get()
                ->map(fn (Service $service) => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'starts_at' => substr($service->starts_at, 0, 5),
                    'ends_at' => substr($service->ends_at, 0, 5),
                    'slot_interval_minutes' => $service->slot_interval_minutes,
                    'default_duration_minutes' => $service->default_duration_minutes,
                    'min_covers' => $service->min_covers,
                    'max_covers' => $service->max_covers,
                    'requires_deposit' => $service->requires_deposit,
                ]),
        ]);
    }

    public function availability(Request $request, BookingAvailability $availability, ?Venue $venue = null): JsonResponse
    {
        $venue = $venue ?: Venue::firstOrFail();
        $validated = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'date' => ['required', 'date'],
            'party_size' => ['required', 'integer', 'min:1', 'max:'.$venue->maximum_party_size],
        ]);

        $service = Service::where('venue_id', $venue->id)->where('is_active', true)->findOrFail($validated['service_id']);
        $date = Carbon::parse($validated['date'], $venue->timezone);

        return response()->json([
            'data' => [
                'date' => $date->toDateString(),
                'service_id' => $service->id,
                'party_size' => (int) $validated['party_size'],
                'slots' => $availability->slots($venue, $service, $date, (int) $validated['party_size'])
                    ->map(fn (Carbon $slot) => [
                        'time' => $slot->format('H:i'),
                        'starts_at' => $slot->toIso8601String(),
                    ])
                    ->values(),
            ],
        ]);
    }

    public function store(Request $request, BookingAvailability $availability, ?Venue $venue = null): JsonResponse
    {
        $venue = $venue ?: Venue::firstOrFail();

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

        $service = Service::where('venue_id', $venue->id)->where('is_active', true)->findOrFail($validated['service_id']);
        $startsAt = Carbon::parse($validated['date'].' '.$validated['time'], $venue->timezone);
        $endsAt = $startsAt->copy()->addMinutes($service->default_duration_minutes);
        $tables = $availability->availableTables($venue, $startsAt, $endsAt, (int) $validated['party_size'], $service);

        if ($tables->isEmpty()) {
            return response()->json([
                'message' => 'No table is available for that date, time and party size.',
                'errors' => [
                    'time' => ['No table is available for that date, time and party size.'],
                ],
            ], 422);
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
            'customer_manage_token' => Str::random(48),
            'party_size' => $validated['party_size'],
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => 'confirmed',
            'source' => 'api',
            'special_requests' => $validated['special_requests'] ?? null,
            'confirmed_at' => now(),
        ]);

        $booking->tables()->attach($tables->pluck('id'));
        Mail::to($customer->email)->send(new BookingConfirmationMail($booking));

        if ($venue->contact_email) {
            Mail::to($venue->contact_email)->send(new NewBookingStaffAlertMail($booking));
        }

        return response()->json([
            'data' => [
                'booking_reference' => $booking->booking_reference,
                'status' => $booking->status,
                'starts_at' => $booking->starts_at->toIso8601String(),
                'ends_at' => $booking->ends_at->toIso8601String(),
                'party_size' => $booking->party_size,
                'service' => $service->name,
                'manage_url' => route('bookings.manage.show', [
                    'booking' => $booking,
                    'token' => $booking->customer_manage_token,
                ]),
            ],
        ], 201);
    }

    private function reference(): string
    {
        do {
            $reference = 'CBR'.Str::upper(Str::random(6));
        } while (Booking::where('booking_reference', $reference)->exists());

        return $reference;
    }
}
