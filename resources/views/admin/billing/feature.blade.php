@extends('layouts.app', ['title' => $title, 'venue' => $venue])

@section('content')
<section class="hero compact">
    <div class="shell">
        <div class="eyebrow">{{ $eyebrow }}</div>
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>
    </div>
</section>

<section class="shell" style="padding-bottom: 48px;">
    <div class="panel empty-state">
        <strong>{{ $title }} workspace</strong>
        <p style="margin: 0;">This module is ready for the next product build-out and is available on your current plan.</p>
    </div>
</section>
@endsection
