@extends('layouts.app', ['title' => 'Add staff user', 'venue' => $venue])

@section('content')
<section class="hero">
    <div class="shell">
        <div class="eyebrow">Staff</div>
        <h1>Add staff user</h1>
        <p>Create credentials for someone who needs backend access.</p>
    </div>
</section>

<section class="shell" style="padding-bottom: 48px;">
    <form class="panel" method="post" action="{{ route('admin.staff.store') }}" style="max-width: 760px;">
        @csrf
        @if ($errors->any())
            <div class="panel errors" style="margin-bottom: 14px;">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0 0 4px;">{{ $error }}</p>
                @endforeach
            </div>
        @endif
        @include('admin.staff._form')
        <div class="actions" style="margin-top: 18px;">
            <button class="primary" type="submit">Create staff user</button>
            <a class="button" href="{{ route('admin.staff.index') }}">Cancel</a>
        </div>
    </form>
</section>
@endsection
