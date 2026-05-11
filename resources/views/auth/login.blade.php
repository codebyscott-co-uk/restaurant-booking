@extends('layouts.app', ['title' => 'Staff login', 'venue' => $venue])

@section('content')
<section class="hero">
    <div class="shell">
        <div class="eyebrow">Staff area</div>
        <h1>Sign in to manage bookings</h1>
        <p>The diary, guest details and operational controls are restricted to staff accounts.</p>
    </div>
</section>

<section class="shell" style="padding-bottom: 48px;">
    <form class="panel" method="post" action="{{ route('login.store') }}" style="max-width: 520px;">
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
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
            </div>
            <label style="display: flex; gap: 10px; align-items: center; font-weight: 700;">
                <input type="checkbox" name="remember" value="1" style="width: 18px; min-height: 18px;">
                Keep me signed in
            </label>
            <button class="primary" type="submit">Sign in</button>
        </div>
    </form>
</section>
@endsection
