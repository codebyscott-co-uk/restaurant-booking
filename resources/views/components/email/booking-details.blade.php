@props(['booking'])

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse; margin-top: 20px;">
    <tr>
        <td style="padding: 11px 0; border-top: 1px solid #dfe7e4;"><strong>Reference</strong></td>
        <td style="padding: 11px 0; border-top: 1px solid #dfe7e4;" align="right">{{ $booking->booking_reference }}</td>
    </tr>
    <tr>
        <td style="padding: 11px 0; border-top: 1px solid #dfe7e4;"><strong>Date</strong></td>
        <td style="padding: 11px 0; border-top: 1px solid #dfe7e4;" align="right">{{ $booking->starts_at->format('l j F Y') }}</td>
    </tr>
    <tr>
        <td style="padding: 11px 0; border-top: 1px solid #dfe7e4;"><strong>Time</strong></td>
        <td style="padding: 11px 0; border-top: 1px solid #dfe7e4;" align="right">{{ $booking->starts_at->format('H:i') }}</td>
    </tr>
    <tr>
        <td style="padding: 11px 0; border-top: 1px solid #dfe7e4;"><strong>Guests</strong></td>
        <td style="padding: 11px 0; border-top: 1px solid #dfe7e4;" align="right">{{ $booking->party_size }}</td>
    </tr>
    <tr>
        <td style="padding: 11px 0; border-top: 1px solid #dfe7e4;"><strong>Service</strong></td>
        <td style="padding: 11px 0; border-top: 1px solid #dfe7e4;" align="right">{{ $booking->service->name }}</td>
    </tr>
</table>
