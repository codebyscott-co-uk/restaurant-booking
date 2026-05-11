@props(['url', 'venue'])

<p style="margin: 24px 0 0;">
    <a href="{{ $url }}" style="display: inline-block; background: {{ $venue->primary_colour }}; color: #ffffff; text-decoration: none; padding: 13px 18px; border-radius: 8px; font-weight: bold;">{{ $slot }}</a>
</p>
