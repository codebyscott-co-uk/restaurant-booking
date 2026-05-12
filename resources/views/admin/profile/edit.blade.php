@extends('layouts.app', ['title' => 'My Profile', 'venue' => $venue])

@section('content')
<section class="hero compact">
    <div class="shell">
        <div class="eyebrow">Account</div>
        <h1>My Profile</h1>
        <p>Update your personal details and avatar used in the staff workspace.</p>
    </div>
</section>

<section class="shell grid settings-grid" style="padding-bottom: 48px;">
    <form class="panel" method="post" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('put')

        @if (session('status'))
            <div class="panel success" style="margin-bottom: 14px;"><p style="margin: 0;">{{ session('status') }}</p></div>
        @endif

        @if ($errors->any())
            <div class="panel errors" style="margin-bottom: 14px;">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0 0 4px;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <h2>Personal details</h2>
        <div class="form-grid">
            <div class="field full">
                <label for="name">Name</label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="field full">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="field">
                <label for="job_title">Job title</label>
                <input id="job_title" name="job_title" value="{{ old('job_title', $user->job_title) }}">
            </div>
            <div class="field">
                <label for="phone">Phone</label>
                <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
            </div>
            <div class="field full">
                <label for="avatar">Avatar</label>
                <input id="avatar" name="avatar" type="file" accept="image/*">
            </div>
            @if ($user->avatar_url)
                <label class="full" style="display: flex; gap: 10px; align-items: center; font-weight: 700;">
                    <input type="checkbox" name="remove_avatar" value="1" style="width: 18px; min-height: 18px;">
                    Remove current avatar
                </label>
            @endif
            <button class="primary full" type="submit">Save profile</button>
        </div>
    </form>

    <aside class="panel">
        <h2>Profile preview</h2>
        <div class="profile-preview">
            @if ($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
            @else
                <span>{{ strtoupper(substr($user->name ?? 'S', 0, 1)) }}</span>
            @endif
            <div>
                <h3>{{ $user->name }}</h3>
                <p style="margin: 0;">{{ $user->email }}</p>
                <div class="table-list" style="margin-top: 10px;">
                    <span class="badge">{{ ucfirst($user->role) }}</span>
                    @if ($user->job_title)<span class="badge">{{ $user->job_title }}</span>@endif
                    @if ($user->phone)<span class="badge">{{ $user->phone }}</span>@endif
                </div>
            </div>
        </div>
    </aside>
</section>
@endsection
