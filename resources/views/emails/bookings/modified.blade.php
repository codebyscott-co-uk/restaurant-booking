<x-email.layout :venue="$booking->venue" title="Booking updated" eyebrow="Booking updated">
    <h1 style="margin: 0 0 14px; font-size: 30px; line-height: 1.12;">Your booking has been updated</h1>
    <p style="margin: 0; color: #62706d;">Hi {{ $booking->customer->first_name }}, here are the latest details for your reservation.</p>

    <x-email.booking-details :booking="$booking" />

    @if ($booking->special_requests)
        <p style="margin: 18px 0 0;"><strong>Requests:</strong> {{ $booking->special_requests }}</p>
    @endif

    <x-email.button :venue="$booking->venue" :url="route('bookings.manage.show', ['booking' => $booking, 'token' => $booking->customer_manage_token])">
        Review booking
    </x-email.button>
</x-email.layout>
