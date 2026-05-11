<x-email.layout :venue="$booking->venue" title="Booking confirmed" eyebrow="Booking confirmed">
    <h1 style="margin: 0 0 14px; font-size: 30px; line-height: 1.12;">Your table is booked</h1>
    <div style="color: #62706d;">
        {!! $booking->venue->email_confirmation_content ?: '<p style="margin: 0;">Thanks for booking with us. We have your reservation and look forward to welcoming you.</p>' !!}
    </div>

    <x-email.booking-details :booking="$booking" />

    @if ($booking->special_requests)
        <p style="margin: 18px 0 0;"><strong>Requests:</strong> {{ $booking->special_requests }}</p>
    @endif

    <x-email.button :venue="$booking->venue" :url="route('bookings.manage.show', ['booking' => $booking, 'token' => $booking->customer_manage_token])">
        Manage booking
    </x-email.button>

    <p style="margin: 18px 0 0; color: #62706d;">{{ $booking->venue->cancellation_policy }}</p>
    <div style="margin-top: 12px; color: #62706d;">
        {!! $booking->venue->email_footer_content ?: '<p style="margin: 0;">Online changes and cancellations close before arrival according to your booking policy.</p>' !!}
    </div>
</x-email.layout>
