@extends('layouts.app', ['title' => 'Add booking', 'venue' => $venue])

@section('content')
<section class="hero compact">
    <div class="shell">
        <div class="eyebrow">Manual booking</div>
        <h1>Add booking</h1>
        <p>Create a phone, walk-in or staff-entered booking with table allocation and availability guidance.</p>
    </div>
</section>

@include('admin.bookings.partials.form', [
    'action' => route('admin.bookings.store'),
    'method' => 'post',
    'submitLabel' => 'Create booking',
])
@endsection
