@extends('layouts.app', ['title' => 'Staff users', 'venue' => $venue])

@section('content')
<section class="hero">
    <div class="shell">
        <div class="eyebrow">Staff</div>
        <h1>Users and staff</h1>
        <p>Create, edit, disable or remove people who can access the backend.</p>
        <a class="button primary" href="{{ route('admin.staff.create') }}">Add staff user</a>
    </div>
</section>

<section class="shell" style="padding-bottom: 48px;">
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

    <div class="staff-list">
        @forelse ($staff as $member)
            <article class="staff-card">
                <div>
                    <h3>{{ $member->name }}</h3>
                    <p style="margin: 0;">{{ $member->email }} · {{ $member->job_title ?: ucfirst($member->role) }}</p>
                    <div class="table-list">
                        <span class="badge">{{ ucfirst($member->role) }}</span>
                        <span class="badge">{{ $member->is_active ? 'Active' : 'Inactive' }}</span>
                        @if ($member->phone)<span class="badge">{{ $member->phone }}</span>@endif
                    </div>
                </div>
                <div class="actions">
                    <a class="button" href="{{ route('admin.staff.edit', $member) }}">Edit</a>
                    <form method="post" action="{{ route('admin.staff.destroy', $member) }}" data-confirm="Delete this staff user?">
                        @csrf
                        @method('delete')
                        <button class="danger" type="submit">Delete</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="empty-state">
                <strong>No staff users yet.</strong>
                <p style="margin: 0;">Create a staff user so your team can manage bookings securely.</p>
                <a class="button primary" href="{{ route('admin.staff.create') }}">Add staff user</a>
            </div>
        @endforelse
    </div>
</section>
@endsection
