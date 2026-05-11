@extends('layouts.app', ['title' => 'Edit staff user', 'venue' => $venue])

@section('content')
<section class="hero">
    <div class="shell">
        <div class="eyebrow">Staff</div>
        <h1>Edit {{ $user->name }}</h1>
        <p>Update profile details, role, account status or password.</p>
    </div>
</section>

<section class="shell" style="padding-bottom: 48px;">
    <form class="panel" method="post" action="{{ route('admin.staff.update', $user) }}" style="max-width: 760px;">
        @csrf
        @method('put')
        @if ($errors->any())
            <div class="panel errors" style="margin-bottom: 14px;">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0 0 4px;">{{ $error }}</p>
                @endforeach
            </div>
        @endif
        @include('admin.staff._form')
        <div class="actions" style="margin-top: 18px;">
            <button class="primary" type="submit">Save staff user</button>
            <a class="button" href="{{ route('admin.staff.index') }}">Cancel</a>
        </div>
    </form>
</section>
@endsection
