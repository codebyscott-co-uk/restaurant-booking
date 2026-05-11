<x-email.layout :venue="$booking->venue" title="Booking cancelled" eyebrow="Booking cancelled">
    <h1 style="margin: 0 0 14px; font-size: 30px; line-height: 1.12;">Your booking has been cancelled</h1>
    <p style="margin: 0; color: #62706d;">Hi {{ $booking->customer->first_name }}, your reservation at {{ $booking->venue->name }} is now cancelled.</p>

    <x-email.booking-details :booking="$booking" />

    <p style="margin: 18px 0 0; color: #62706d;">Need another table? You can make a fresh booking whenever you are ready.</p>

    <x-email.button :venue="$booking->venue" :url="route('bookings.create')">
        Book again
    </x-email.button>
</x-email.layout>
