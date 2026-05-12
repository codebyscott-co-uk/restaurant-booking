@extends('layouts.app', ['title' => 'Start your Resora OS workspace', 'venue' => $venue])

@section('content')
<section class="hero">
    <div class="shell">
        <div class="eyebrow">Resora OS onboarding</div>
        <h1>Start your hospitality workspace</h1>
        <p>Create a venue account with starter services, tables and opening hours. You can customise everything after sign up.</p>
    </div>
</section>

<section class="shell grid booking-grid" style="padding-bottom: 48px;">
    <form class="panel" method="post" action="{{ route('signup.store') }}">
        @csrf

        <h2>Venue details</h2>

        @if ($errors->any())
            <div class="panel errors" style="margin-bottom: 14px;">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0 0 4px;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="form-grid">
            <div class="field full">
                <label for="business_name">Venue name</label>
                <input id="business_name" name="business_name" value="{{ old('business_name') }}" autocomplete="organization" required autofocus>
            </div>
            <div class="field">
                <label for="contact_email">Venue email</label>
                <input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email') }}" autocomplete="email" required>
            </div>
            <div class="field">
                <label for="phone">Venue phone</label>
                <input id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel">
            </div>
            <div class="field full">
                <label for="city">Town or city</label>
                <input id="city" name="city" value="{{ old('city') }}" autocomplete="address-level2">
            </div>
        </div>

        <h2 style="margin-top: 22px;">Owner account</h2>
        <div class="form-grid">
            <div class="field">
                <label for="owner_name">Your name</label>
                <input id="owner_name" name="owner_name" value="{{ old('owner_name') }}" autocomplete="name" required>
            </div>
            <div class="field">
                <label for="owner_email">Your login email</label>
                <input id="owner_email" name="owner_email" type="email" value="{{ old('owner_email') }}" autocomplete="username" required>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required>
            </div>
            <div class="field">
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            </div>
            <button class="primary full" type="submit">Create workspace</button>
        </div>
    </form>

    <aside class="grid">
        <div class="panel">
            <h2>What gets created</h2>
            <div class="table-list">
                <span class="badge">Tenant workspace</span>
                <span class="badge">Owner login</span>
                <span class="badge">Lunch service</span>
                <span class="badge">Dinner service</span>
                <span class="badge">Starter tables</span>
                <span class="badge">Booking widget</span>
            </div>
            <p style="margin-bottom: 0;">This is the first step towards self-serve SaaS onboarding. Billing and plan selection can plug into this flow next.</p>
        </div>

        <div class="notice">
            <strong>Already have an account?</strong>
            <p>Staff users can sign in to manage bookings, settings and availability.</p>
            <a class="button" href="{{ route('login') }}">Staff login</a>
        </div>
    </aside>
</section>
@endsection
