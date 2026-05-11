<x-email.layout :venue="$booking->venue" title="Booking updated" eyebrow="Booking updated">
    <h1 style="margin: 0 0 14px; font-size: 30px; line-height: 1.12;">Your booking has been updated</h1>
    <div style="color: #62706d;">
        {!! $booking->venue->email_modification_content ?: '<p style="margin: 0;">Your booking has been updated. Please check the latest details below.</p>' !!}
    </div>

    <x-email.booking-details :booking="$booking" />

    @if ($booking->special_requests)
        <p style="margin: 18px 0 0;"><strong>Requests:</strong> {{ $booking->special_requests }}</p>
    @endif

    <x-email.button :venue="$booking->venue" :url="route('bookings.manage.show', ['booking' => $booking, 'token' => $booking->customer_manage_token])">
        Review booking
    </x-email.button>

    <div style="margin-top: 12px; color: #62706d;">
        {!! $booking->venue->email_footer_content ?: '<p style="margin: 0;">Online changes and cancellations close before arrival according to your booking policy.</p>' !!}
    </div>
</x-email.layout>
