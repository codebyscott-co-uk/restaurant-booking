@extends('layouts.app', ['title' => 'Services', 'venue' => $venue])

@section('content')
<section class="hero">
    <div class="shell">
        <div class="eyebrow">Services</div>
        <h1>Booking services</h1>
        <p>Manage lunch, dinner and other bookable service windows.</p>
        <a class="button primary" href="{{ route('admin.services.create') }}">Add service</a>
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
        @forelse ($services as $service)
            <article class="staff-card">
                <div>
                    <h3>{{ $service->name }}</h3>
                    <p style="margin: 0;">
                        {{ substr($service->starts_at, 0, 5) }} to {{ substr($service->ends_at, 0, 5) }}
                        · {{ $service->default_duration_minutes }} mins
                        · {{ $service->min_covers }}-{{ $service->max_covers }} guests
                    </p>
                    <div class="table-list">
                        <span class="badge">{{ $service->slot_interval_minutes }} min slots</span>
                        <span class="badge">{{ $service->requires_deposit ? 'Deposit required' : 'No deposit' }}</span>
                        <span class="badge">{{ $service->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                </div>
                <div class="actions">
                    <a class="button" href="{{ route('admin.services.edit', $service) }}">Edit</a>
                    <form method="post" action="{{ route('admin.services.destroy', $service) }}" data-confirm="Delete this service?">
                        @csrf
                        @method('delete')
                        <button class="danger" type="submit">Delete</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="empty-state">
                <strong>No services yet.</strong>
                <p style="margin: 0;">Add lunch, dinner or event services so guests can book online.</p>
                <a class="button primary" href="{{ route('admin.services.create') }}">Add service</a>
            </div>
        @endforelse
    </div>
</section>
@endsection
