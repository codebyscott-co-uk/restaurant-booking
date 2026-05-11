@extends('layouts.app', ['title' => 'Tables and areas', 'venue' => $venue])

@section('content')
<section class="hero">
    <div class="shell">
        <div class="eyebrow">Tables</div>
        <h1>Dining areas and tables</h1>
        <p>Manage rooms, terraces and bookable tables used by the availability engine.</p>
        <div class="actions">
            <a class="button primary" href="{{ route('admin.tables.create') }}">Add table</a>
            <a class="button" href="{{ route('admin.areas.create') }}">Add dining area</a>
        </div>
    </div>
</section>

<section class="shell grid booking-grid" style="padding-bottom: 48px;">
    <div class="panel">
        <h2>Tables</h2>
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
            @forelse ($tables as $table)
                <article class="staff-card">
                    <div>
                        <h3>{{ $table->name }}</h3>
                        <p style="margin: 0;">{{ $table->diningArea->name }} · {{ $table->min_covers }}-{{ $table->max_covers }} guests</p>
                        <div class="table-list">
                            <span class="badge">{{ $table->is_joinable ? 'Joinable' : 'Standalone' }}</span>
                            <span class="badge">{{ $table->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                    </div>
                    <div class="actions">
                        <a class="button" href="{{ route('admin.tables.edit', $table) }}">Edit</a>
                        <form method="post" action="{{ route('admin.tables.destroy', $table) }}" data-confirm="Delete this table?">
                            @csrf
                            @method('delete')
                            <button class="danger" type="submit">Delete</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="empty-state">
                    <strong>No tables yet.</strong>
                    <p style="margin: 0;">Add tables so the availability engine can allocate bookings.</p>
                    <a class="button primary" href="{{ route('admin.tables.create') }}">Add table</a>
                </div>
            @endforelse
        </div>
    </div>

    <aside class="panel">
        <h2>Dining areas</h2>
        <div class="staff-list">
            @forelse ($areas as $area)
                <article class="staff-card">
                    <div>
                    <h3>{{ $area->name }}</h3>
                    <p style="margin: 0;">{{ $area->description ?: 'No description' }}</p>
                    <div class="table-list">
                        <span class="badge">{{ $area->tables->count() }} tables</span>
                        <span class="badge">{{ $area->is_active ? 'Active' : 'Inactive' }}</span>
                        <span class="badge">Sort {{ $area->sort_order }}</span>
                    </div>
                    </div>
                    <div class="actions" style="margin-top: 12px;">
                        <a class="button" href="{{ route('admin.areas.edit', $area) }}">Edit</a>
                        <form method="post" action="{{ route('admin.areas.destroy', $area) }}" data-confirm="Delete this dining area?">
                            @csrf
                            @method('delete')
                            <button class="danger" type="submit">Delete</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="empty-state">
                    <strong>No dining areas yet.</strong>
                    <p style="margin: 0;">Create an area before adding tables to the booking layout.</p>
                    <a class="button primary" href="{{ route('admin.areas.create') }}">Add dining area</a>
                </div>
            @endforelse
        </div>
    </aside>
</section>
@endsection
