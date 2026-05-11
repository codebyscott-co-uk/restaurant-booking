@extends('layouts.app', ['title' => 'Edit dining area', 'venue' => $venue])

@section('content')
<section class="hero">
    <div class="shell">
        <div class="eyebrow">Tables</div>
        <h1>Edit {{ $area->name }}</h1>
        <p>Update the dining area name, description and active status.</p>
    </div>
</section>

<section class="shell" style="padding-bottom: 48px;">
    <form class="panel" method="post" action="{{ route('admin.areas.update', $area) }}" style="max-width: 760px;">
        @csrf
        @method('put')
        @if ($errors->any())
            <div class="panel errors" style="margin-bottom: 14px;">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0 0 4px;">{{ $error }}</p>
                @endforeach
            </div>
        @endif
        @include('admin.areas._form')
        <div class="actions" style="margin-top: 18px;">
            <button class="primary" type="submit">Save dining area</button>
            <a class="button" href="{{ route('admin.areas.index') }}">Cancel</a>
        </div>
    </form>
</section>
@endsection

