@props(['venue', 'title', 'eyebrow' => null])

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
</head>
<body style="margin: 0; font-family: Arial, sans-serif; color: #17211f; background: #f7f8f4;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #f7f8f4; padding: 26px 14px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 640px; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 0 4px 16px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td>
                                        @if ($venue->logo_url)
                                            <img src="{{ $venue->logo_url }}" width="110" alt="{{ $venue->name }}" style="display: block; width: 110px; max-width: 160px; height: auto; margin-bottom: 10px;">
                                        @endif
                                        <p style="margin: 0; color: #62706d; font-size: 13px;">{{ $venue->name }}</p>
                                    </td>
                                    <td align="right" style="color: #62706d; font-size: 12px;">
                                        {{ $venue->phone ?: $venue->contact_email }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background: #ffffff; border: 1px solid #dfe7e4; border-radius: 8px; overflow: hidden; box-shadow: 0 18px 50px rgba(17,24,39,.10);">
                            <div style="height: 6px; background: {{ $venue->primary_colour }};"></div>
                            <div style="padding: 28px;">
                                @if ($eyebrow)
                                    <p style="margin: 0 0 8px; color: {{ $venue->primary_colour }}; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: .08em;">{{ $eyebrow }}</p>
                                @endif

                                {{ $slot }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 16px 8px 0; color: #62706d; font-size: 12px;">
                            Resora OS hospitality operations software by Code by Scott.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
