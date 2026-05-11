@extends('layouts.app', ['title' => 'Manage booking', 'venue' => $venue])

@section('content')
<section class="hero">
    <div class="shell">
        <div class="eyebrow">Manage booking</div>
        <h1>Find your reservation</h1>
        <p>Use your booking reference and email address to securely view, change or cancel your table.</p>
    </div>
</section>

<section class="shell" style="padding-bottom: 48px;">
    <form class="panel" method="post" action="{{ route('bookings.lookup.find') }}" style="max-width: 560px;">
        @csrf

        @if ($errors->any())
            <div class="panel errors" style="margin-bottom: 14px;">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0 0 4px;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="grid">
            <div class="field">
                <label for="booking_reference">Booking reference</label>
                <input id="booking_reference" name="booking_reference" value="{{ old('booking_reference') }}" placeholder="CBR123ABC" required>
            </div>
            <div class="field">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
            </div>
            <button class="primary" type="submit">Find booking</button>
        </div>
    </form>
</section>
@endsection
