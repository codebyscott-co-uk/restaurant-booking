@extends('layouts.app', ['title' => 'Add service', 'venue' => $venue])

@section('content')
<section class="hero">
    <div class="shell">
        <div class="eyebrow">Services</div>
        <h1>Add service</h1>
        <p>Create a new bookable service window.</p>
    </div>
</section>

<section class="shell" style="padding-bottom: 48px;">
    <form class="panel" method="post" action="{{ route('admin.services.store') }}" style="max-width: 760px;">
        @csrf
        @if ($errors->any())
            <div class="panel errors" style="margin-bottom: 14px;">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0 0 4px;">{{ $error }}</p>
                @endforeach
            </div>
        @endif
        @include('admin.services._form')
        <div class="actions" style="margin-top: 18px;">
            <button class="primary" type="submit">Create service</button>
            <a class="button" href="{{ route('admin.services.index') }}">Cancel</a>
        </div>
    </form>
</section>
@endsection
