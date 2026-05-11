<x-email.layout :venue="$booking->venue" title="Booking cancelled" eyebrow="Booking cancelled">
    <h1 style="margin: 0 0 14px; font-size: 30px; line-height: 1.12;">Your booking has been cancelled</h1>
    <div style="color: #62706d;">
        {!! $booking->venue->email_cancellation_content ?: '<p style="margin: 0;">Your booking has been cancelled. We hope to welcome you another time.</p>' !!}
    </div>

    <x-email.booking-details :booking="$booking" />

    <p style="margin: 18px 0 0; color: #62706d;">Need another table? You can make a fresh booking whenever you are ready.</p>

    <x-email.button :venue="$booking->venue" :url="route('bookings.create')">
        Book again
    </x-email.button>

    <div style="margin-top: 12px; color: #62706d;">
        {!! $booking->venue->email_footer_content ?: '<p style="margin: 0;">Online changes and cancellations close before arrival according to your booking policy.</p>' !!}
    </div>
</x-email.layout>
