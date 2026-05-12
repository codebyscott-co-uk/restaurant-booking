@extends('layouts.app', ['title' => 'Edit booking', 'venue' => $venue])

@section('content')
<section class="hero compact">
    <div class="shell">
        <div class="eyebrow">Booking management</div>
        <h1>Edit {{ $booking->booking_reference }}</h1>
        <p>Update guest details, service timing, table assignment, status and internal notes.</p>
    </div>
</section>

@include('admin.bookings.partials.form', [
    'action' => route('admin.bookings.update', $booking),
    'method' => 'put',
    'submitLabel' => 'Save booking',
])
@endsection
