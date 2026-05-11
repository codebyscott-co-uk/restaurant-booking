@extends('layouts.app', ['title' => 'Add dining area', 'venue' => $venue])

@section('content')
<section class="hero">
    <div class="shell">
        <div class="eyebrow">Tables</div>
        <h1>Add dining area</h1>
        <p>Create a new room, terrace or bookable section.</p>
    </div>
</section>

<section class="shell" style="padding-bottom: 48px;">
    <form class="panel" method="post" action="{{ route('admin.areas.store') }}" style="max-width: 760px;">
        @csrf
        @if ($errors->any())
            <div class="panel errors" style="margin-bottom: 14px;">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0 0 4px;">{{ $error }}</p>
                @endforeach
            </div>
        @endif
        @include('admin.areas._form')
        <div class="actions" style="margin-top: 18px;">
            <button class="primary" type="submit">Create dining area</button>
            <a class="button" href="{{ route('admin.areas.index') }}">Cancel</a>
        </div>
    </form>
</section>
@endsection

