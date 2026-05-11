<x-email.layout :venue="$booking->venue" title="Booking confirmed" eyebrow="Booking confirmed">
    <h1 style="margin: 0 0 14px; font-size: 30px; line-height: 1.12;">Your table is booked</h1>
    <p style="margin: 0; color: #62706d;">Thanks {{ $booking->customer->first_name }}, we have your reservation at {{ $booking->venue->name }}.</p>

    <x-email.booking-details :booking="$booking" />

    @if ($booking->special_requests)
        <p style="margin: 18px 0 0;"><strong>Requests:</strong> {{ $booking->special_requests }}</p>
    @endif

    <x-email.button :venue="$booking->venue" :url="route('bookings.manage.show', ['booking' => $booking, 'token' => $booking->customer_manage_token])">
        Manage booking
    </x-email.button>

    <p style="margin: 18px 0 0; color: #62706d;">{{ $booking->venue->cancellation_policy }}</p>
    <p style="margin: 12px 0 0; color: #62706d;">Online changes and cancellations close {{ $booking->venue->cancellation_notice_hours }} hours before arrival.</p>
</x-email.layout>
