<x-email.layout :venue="$booking->venue" title="Booking reminder" eyebrow="Booking reminder">
    <h1 style="margin: 0 0 14px; font-size: 30px; line-height: 1.12;">We’ll see you soon</h1>
    <div style="color: #62706d;">
        {!! $booking->venue->email_reminder_content ?: '<p style="margin: 0;">This is a friendly reminder about your upcoming reservation. We look forward to seeing you soon.</p>' !!}
    </div>

    <x-email.booking-details :booking="$booking" />

    <x-email.button :venue="$booking->venue" :url="route('bookings.manage.show', ['booking' => $booking, 'token' => $booking->customer_manage_token])">
        View booking
    </x-email.button>

    <div style="margin-top: 12px; color: #62706d;">
        {!! $booking->venue->email_footer_content ?: '<p style="margin: 0;">Online changes and cancellations close before arrival according to your booking policy.</p>' !!}
    </div>
</x-email.layout>
