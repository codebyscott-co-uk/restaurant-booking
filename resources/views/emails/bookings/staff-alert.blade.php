<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>New booking</title>
</head>
<body style="margin: 0; font-family: Arial, sans-serif; color: #17211f; background: #fbfbf7;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #fbfbf7; padding: 24px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 620px; background: #ffffff; border: 1px solid #dfe7e4; border-radius: 8px; overflow: hidden;">
                    <tr>
                        <td style="padding: 24px;">
                            <p style="margin: 0 0 8px; color: {{ $booking->venue->primary_colour }}; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: .08em;">New online booking</p>
                            <h1 style="margin: 0 0 16px; font-size: 28px; line-height: 1.15;">{{ $booking->customer->full_name }}</h1>
                            <p style="margin: 0 0 18px; color: #5f6f6b;">{{ $booking->party_size }} guests for {{ $booking->service->name }}.</p>

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
                                    <td style="padding: 10px 0; border-top: 1px solid #dfe7e4;"><strong>Phone</strong></td>
                                    <td style="padding: 10px 0; border-top: 1px solid #dfe7e4;" align="right">{{ $booking->customer->phone }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0; border-top: 1px solid #dfe7e4;"><strong>Email</strong></td>
                                    <td style="padding: 10px 0; border-top: 1px solid #dfe7e4;" align="right">{{ $booking->customer->email }}</td>
                                </tr>
                            </table>

                            @if ($booking->special_requests)
                                <p style="margin: 18px 0 0;"><strong>Requests:</strong> {{ $booking->special_requests }}</p>
                            @endif

                            <p style="margin: 18px 0 0; color: #5f6f6b;">Allocated tables: {{ $booking->tables->pluck('name')->join(', ') ?: 'To be assigned' }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

