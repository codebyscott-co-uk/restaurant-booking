@extends('layouts.app', ['title' => 'Edit service', 'venue' => $venue])

@section('content')
<section class="hero">
    <div class="shell">
        <div class="eyebrow">Services</div>
        <h1>Edit {{ $service->name }}</h1>
        <p>Update service times, party size limits and booking rules.</p>
    </div>
</section>

<section class="shell" style="padding-bottom: 48px;">
    <form class="panel" method="post" action="{{ route('admin.services.update', $service) }}" style="max-width: 760px;">
        @csrf
        @method('put')
        @if ($errors->any())
            <div class="panel errors" style="margin-bottom: 14px;">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0 0 4px;">{{ $error }}</p>
                @endforeach
            </div>
        @endif
        @include('admin.services._form')
        <div class="actions" style="margin-top: 18px;">
            <button class="primary" type="submit">Save service</button>
            <a class="button" href="{{ route('admin.services.index') }}">Cancel</a>
        </div>
    </form>
</section>
@endsection
