<x-email.layout :venue="$booking->venue" title="Booking reminder" eyebrow="Booking reminder">
    <h1 style="margin: 0 0 14px; font-size: 30px; line-height: 1.12;">We’ll see you soon</h1>
    <p style="margin: 0; color: #62706d;">Hi {{ $booking->customer->first_name }}, this is a reminder for your upcoming reservation at {{ $booking->venue->name }}.</p>

    <x-email.booking-details :booking="$booking" />

    <x-email.button :venue="$booking->venue" :url="route('bookings.manage.show', ['booking' => $booking, 'token' => $booking->customer_manage_token])">
        View booking
    </x-email.button>

    <p style="margin: 18px 0 0; color: #62706d;">Tables are held for 15 minutes. Please contact us if you are running late.</p>
</x-email.layout>
