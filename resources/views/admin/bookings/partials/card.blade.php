<article class="booking-card premium-booking-card">
    <div class="booking-time">
        <strong>{{ $booking->starts_at->format('H:i') }}</strong>
        <span class="muted">{{ $booking->ends_at->format('H:i') }}</span>
        <span class="badge status-badge {{ $statusMeta[$booking->status]['class'] ?? 'slate' }}">{{ $statusMeta[$booking->status]['label'] ?? ucfirst($booking->status) }}</span>
    </div>
    <div>
        <h3>{{ $booking->customer->full_name }} · {{ $booking->party_size }} guests</h3>
        <div class="booking-meta">
            <span class="badge">{{ $booking->booking_reference }}</span>
            <span class="badge">{{ $sourceLabels[$booking->source] ?? ucfirst(str_replace('_', ' ', $booking->source)) }}</span>
            @forelse ($booking->tables as $table)
                <span class="badge">{{ $table->name }} · {{ $table->diningArea->name }}</span>
            @empty
                <span class="badge">Unassigned</span>
            @endforelse
            @if ($booking->special_requests)
                <span class="badge request-badge">Special request</span>
            @endif
        </div>
        @if ($booking->special_requests)
            <p class="booking-note">{{ $booking->special_requests }}</p>
        @endif
    </div>
    <div class="booking-actions">
        <a class="button subtle" href="#booking-{{ $booking->id }}">Details</a>
        <a class="button" href="{{ route('admin.bookings.edit', $booking) }}">Edit</a>
    </div>
</article>
