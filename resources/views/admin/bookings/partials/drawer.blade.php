<div class="booking-drawer" id="booking-{{ $booking->id }}">
    <a class="booking-drawer-backdrop" href="#"></a>
    <aside class="booking-drawer-panel" aria-labelledby="booking-title-{{ $booking->id }}">
        <div class="booking-drawer-head">
            <div>
                <span class="badge status-badge {{ $statusMeta[$booking->status]['class'] ?? 'slate' }}">{{ $statusMeta[$booking->status]['label'] ?? ucfirst($booking->status) }}</span>
                <h2 id="booking-title-{{ $booking->id }}">{{ $booking->customer->full_name }}</h2>
                <p>{{ $booking->booking_reference }} · {{ $sourceLabels[$booking->source] ?? ucfirst(str_replace('_', ' ', $booking->source)) }}</p>
            </div>
            <a class="button" href="#">Close</a>
        </div>

        <div class="booking-detail-grid">
            <span><strong>Email</strong>{{ $booking->customer->email }}</span>
            <span><strong>Phone</strong>{{ $booking->customer->phone }}</span>
            <span><strong>Date</strong>{{ $booking->starts_at->format('D j M Y') }}</span>
            <span><strong>Time</strong>{{ $booking->starts_at->format('H:i') }} to {{ $booking->ends_at->format('H:i') }}</span>
            <span><strong>Service</strong>{{ $booking->service->name }}</span>
            <span><strong>Covers</strong>{{ $booking->party_size }}</span>
            <span class="full"><strong>Tables</strong>{{ $booking->tables->map(fn ($table) => $table->name.' · '.$table->diningArea->name)->join(', ') ?: 'Unassigned' }}</span>
        </div>

        <div class="booking-drawer-section">
            <h3>Guest request</h3>
            <p>{{ $booking->special_requests ?: 'No special requests recorded.' }}</p>
        </div>
        @if ($canUseCrm ?? false)
            <div class="booking-drawer-section">
                <h3>CRM profile</h3>
                <p>
                    {{ $booking->customer->is_vip ? 'VIP guest. ' : '' }}
                    {{ $booking->customer->notes ?: 'No customer notes recorded.' }}
                </p>
                @if ($booking->customer->allergies || $booking->customer->dietary_requirements || $booking->customer->preferences)
                    <div class="crm-badge-row">
                        @if ($booking->customer->allergies)
                            <span class="badge status-badge red">Allergies</span>
                        @endif
                        @if ($booking->customer->dietary_requirements)
                            <span class="badge status-badge green">Dietary</span>
                        @endif
                        @if ($booking->customer->preferences)
                            <span class="badge status-badge cyan">Preferences</span>
                        @endif
                    </div>
                    <p>{{ collect([$booking->customer->allergies, $booking->customer->dietary_requirements, $booking->customer->preferences])->filter()->join(' · ') }}</p>
                @endif
                <a class="button subtle" href="{{ route('admin.customers.show', $booking->customer) }}">Open customer profile</a>
            </div>
        @endif
        <form class="booking-drawer-section" method="post" action="{{ route('admin.bookings.notes.update', $booking) }}">
            @csrf
            @method('patch')
            <label for="internal-notes-{{ $booking->id }}">Internal notes</label>
            <textarea id="internal-notes-{{ $booking->id }}" name="internal_notes">{{ old('internal_notes', $booking->internal_notes) }}</textarea>
            <button class="subtle" type="submit">Save notes</button>
        </form>

        <div class="booking-drawer-section">
            <h3>Quick actions</h3>
            <div class="status-action-grid">
                @foreach (\App\Models\Booking::STATUSES as $nextStatus)
                    <form method="post" action="{{ route('admin.bookings.status.update', $booking) }}">
                        @csrf
                        @method('patch')
                        <input type="hidden" name="status" value="{{ $nextStatus }}">
                        <button class="{{ $booking->status === $nextStatus ? 'primary' : 'subtle' }}" type="submit">
                            {{ ucfirst(str_replace('_', ' ', $nextStatus)) }}
                        </button>
                    </form>
                @endforeach
            </div>
        </div>

        <div class="booking-drawer-section">
            <h3>Timeline</h3>
            <p>Created {{ $booking->created_at->format('d M Y H:i') }} · Updated {{ $booking->updated_at->format('d M Y H:i') }}</p>
            <a class="button primary" href="{{ route('admin.bookings.edit', $booking) }}">Edit booking and tables</a>
        </div>
    </aside>
</div>
