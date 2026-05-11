<x-email.layout :venue="$booking->venue" title="New booking" eyebrow="Staff alert">
    <h1 style="margin: 0 0 14px; font-size: 30px; line-height: 1.12;">New booking: {{ $booking->customer->full_name }}</h1>
    <p style="margin: 0; color: #62706d;">{{ $booking->party_size }} guests for {{ $booking->service->name }}.</p>

    <x-email.booking-details :booking="$booking" />

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse; margin-top: 4px;">
        <tr>
            <td style="padding: 11px 0; border-top: 1px solid #dfe7e4;"><strong>Phone</strong></td>
            <td style="padding: 11px 0; border-top: 1px solid #dfe7e4;" align="right">{{ $booking->customer->phone }}</td>
        </tr>
        <tr>
            <td style="padding: 11px 0; border-top: 1px solid #dfe7e4;"><strong>Email</strong></td>
            <td style="padding: 11px 0; border-top: 1px solid #dfe7e4;" align="right">{{ $booking->customer->email }}</td>
        </tr>
    </table>

    @if ($booking->special_requests)
        <p style="margin: 18px 0 0;"><strong>Requests:</strong> {{ $booking->special_requests }}</p>
    @endif

    <p style="margin: 18px 0 0; color: #62706d;">Allocated tables: {{ $booking->tables->pluck('name')->join(', ') ?: 'To be assigned' }}</p>
</x-email.layout>
