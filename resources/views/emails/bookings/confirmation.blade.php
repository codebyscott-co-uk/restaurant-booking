<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Booking confirmed</title>
</head>
<body style="margin: 0; font-family: Arial, sans-serif; color: #17211f; background: #fbfbf7;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #fbfbf7; padding: 24px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 620px; background: #ffffff; border: 1px solid #dfe7e4; border-radius: 8px; overflow: hidden;">
                    <tr>
                        <td style="padding: 24px;">
                            <p style="margin: 0 0 8px; color: {{ $booking->venue->primary_colour }}; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: .08em;">Booking confirmed</p>
                            <h1 style="margin: 0 0 16px; font-size: 28px; line-height: 1.15;">{{ $booking->venue->name }}</h1>
                            <p style="margin: 0 0 18px; color: #5f6f6b;">Thanks {{ $booking->customer->first_name }}, your table is booked.</p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 10px 0; border-top: 1px solid #dfe7e4;"><strong>Reference</strong></td>
                                    <td style="padding: 10px 0; border-top: 1px solid #dfe7e4;" align="right">{{ $booking->booking_reference }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0; border-top: 1px solid #dfe7e4;"><strong>Date</strong></td>
                                    <td style="padding: 10px 0; border-top: 1px solid #dfe7e4;" align="right">{{ $booking->starts_at->format('l j F Y') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0; border-top: 1px solid #dfe7e4;"><strong>Time</strong></td>
                                    <td style="padding: 10px 0; border-top: 1px solid #dfe7e4;" align="right">{{ $booking->starts_at->format('H:i') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0; border-top: 1px solid #dfe7e4;"><strong>Guests</strong></td>
                                    <td style="padding: 10px 0; border-top: 1px solid #dfe7e4;" align="right">{{ $booking->party_size }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0; border-top: 1px solid #dfe7e4;"><strong>Service</strong></td>
                                    <td style="padding: 10px 0; border-top: 1px solid #dfe7e4;" align="right">{{ $booking->service->name }}</td>
                                </tr>
                            </table>

                            @if ($booking->special_requests)
                                <p style="margin: 18px 0 0;"><strong>Requests:</strong> {{ $booking->special_requests }}</p>
                            @endif

                            <p style="margin: 18px 0 0; color: #5f6f6b;">{{ $booking->venue->cancellation_policy }}</p>
                            <p style="margin: 18px 0 0; color: #5f6f6b;">If you need to change anything, please contact {{ $booking->venue->phone ?: $booking->venue->contact_email }}.</p>
                        </td>
                    </tr>
                </table>
                <p style="margin: 16px 0 0; color: #5f6f6b; font-size: 12px;">Powered by Code by Scott</p>
            </td>
        </tr>
    </table>
</body>
</html>

