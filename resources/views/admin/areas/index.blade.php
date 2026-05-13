@extends('layouts.app', ['title' => 'Tables and areas', 'venue' => $venue])

@section('content')
@php
    $query = fn (array $merge = []) => array_filter(array_merge(request()->query(), $merge), fn ($value) => $value !== null && $value !== '');
@endphp

<section class="hero compact tables-areas-hero">
    <div class="shell">
        <div class="dashboard-hero-grid">
            <div>
                <div class="eyebrow">Tables & Areas</div>
                <h1>Floor setup</h1>
                <p>Manage dining areas, table capacity, active availability and booking-safe table controls for {{ $venue->name }}.</p>
            </div>
            <div class="insight-card gradient-violet">
                <div>
                    <span>Active capacity</span>
                    <strong>{{ $summary['active_capacity'] }}</strong>
                    <p>{{ $summary['active_tables'] }} active tables · {{ $summary['future_bookings'] }} future bookings</p>
                </div>
                <div class="orbital-chart" style="--value: {{ min(100, $summary['active_capacity'] * 2) }};">
                    <span>{{ $summary['active_tables'] }}</span>
                </div>
            </div>
        </div>
        <div class="actions">
            <a class="button primary" href="{{ route('admin.tables.create') }}">Add table</a>
            <a class="button subtle" href="{{ route('admin.areas.create') }}">Add area</a>
        </div>
    </div>
</section>

<section class="shell tables-areas-suite">
    @if (session('status'))
        <div class="panel success"><p style="margin: 0;">{{ session('status') }}</p></div>
    @endif

    @if ($errors->any())
        <div class="panel errors">
            @foreach ($errors->all() as $error)
                <p style="margin: 0 0 4px;">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="crm-summary-grid">
        <article class="booking-summary-card violet"><span>Dining areas</span><strong>{{ $summary['areas'] }}</strong><small>Sections and rooms</small></article>
        <article class="booking-summary-card green"><span>Active tables</span><strong>{{ $summary['active_tables'] }}</strong><small>Available for allocation</small></article>
        <article class="booking-summary-card cyan"><span>Total capacity</span><strong>{{ $summary['active_capacity'] }}</strong><small>Active max covers</small></article>
        <article class="booking-summary-card slate"><span>Inactive tables</span><strong>{{ $summary['inactive_tables'] }}</strong><small>Hidden from availability</small></article>
    </div>

    <div class="panel floorplan-prompt">
        <div>
            <span class="badge status-badge {{ $canUseFloorplan ? 'violet' : 'slate' }}">{{ $canUseFloorplan ? 'Premium ready' : 'Premium' }}</span>
            <h2>Visual Floorplan</h2>
            <p>Drag-and-drop visual floor planning is a Premium module planned for a future release. Your areas and tables here will power that layout.</p>
        </div>
        @if (! $canUseFloorplan)
            <a class="button primary" href="{{ route('admin.billing.index') }}">Upgrade for floorplan tools</a>
        @else
            <span class="button subtle">Coming soon</span>
        @endif
    </div>

    <div class="panel table-combinations-panel">
        <div class="widget-heading">
            <div>
                <h2>Table combinations</h2>
                <p>Joined table allocation already works through joinable tables. Common saved combinations such as T1 + T2 will become a guided workflow later.</p>
            </div>
            @if (! $canUseTableCombinations)
                <a class="button subtle" href="{{ route('admin.billing.index') }}">Professional feature</a>
            @else
                <span class="badge status-badge violet">Professional ready</span>
            @endif
        </div>
        <div class="combination-preview">
            @foreach ($tables->where('is_joinable', true)->where('is_active', true)->take(4) as $table)
                <span>{{ $table->name }} · {{ $table->max_covers }}</span>
            @endforeach
            @if ($tables->where('is_joinable', true)->where('is_active', true)->isEmpty())
                <span>No joinable active tables configured yet.</span>
            @endif
        </div>
    </div>

    <div class="view-switch booking-view-switch">
        <a class="button {{ $display === 'grid' ? 'primary' : 'subtle' }}" href="{{ route('admin.areas.index', $query(['display' => 'grid'])) }}">Visual grid</a>
        <a class="button {{ $display === 'list' ? 'primary' : 'subtle' }}" href="{{ route('admin.areas.index', $query(['display' => 'list'])) }}">Table list</a>
    </div>

    @if ($areas->isEmpty())
        <div class="empty-state">
            <strong>No dining areas configured.</strong>
            <p style="margin: 0;">Create areas such as Main Restaurant, Bar, Garden, Private Dining or Terrace before adding tables.</p>
            <a class="button primary" href="{{ route('admin.areas.create') }}">Add area</a>
        </div>
    @elseif ($tables->isEmpty())
        <div class="empty-state">
            <strong>No tables configured.</strong>
            <p style="margin: 0;">Add tables so bookings can be assigned and availability can calculate capacity.</p>
            <a class="button primary" href="{{ route('admin.tables.create') }}">Add table</a>
        </div>
    @elseif ($display === 'list')
        <div class="panel dashboard-widget">
            <div class="widget-heading">
                <div>
                    <h2>Table list</h2>
                    <p>Compact operations view for capacity, active status, future bookings and safe actions.</p>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Table</th>
                            <th>Area</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th>Future bookings</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tables as $table)
                            <tr>
                                <td><strong>{{ $table->name }}</strong><br><small>{{ $table->is_joinable ? 'Joinable' : 'Standalone' }}</small></td>
                                <td>{{ $table->diningArea->name }}</td>
                                <td>{{ $table->min_covers }}-{{ $table->max_covers }} covers</td>
                                <td><span class="badge status-badge {{ $table->is_active ? 'green' : 'slate' }}">{{ $table->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td>{{ $table->future_bookings_count }}</td>
                                <td>{{ $table->updated_at->format('d M Y') }}</td>
                                <td>@include('admin.areas.partials.table-actions', ['table' => $table])</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="areas-grid">
            @foreach ($areas as $area)
                @php
                    $areaActiveTables = $area->tables->where('is_active', true);
                    $areaCapacity = $areaActiveTables->sum('max_covers');
                @endphp
                <section class="panel area-section-card {{ $area->is_active ? '' : 'inactive' }}">
                    <div class="widget-heading">
                        <div>
                            <span class="badge status-badge {{ $area->is_active ? 'green' : 'slate' }}">{{ $area->is_active ? 'Active area' : 'Inactive area' }}</span>
                            <h2>{{ $area->name }}</h2>
                            <p>{{ $area->description ?: 'No description recorded.' }}</p>
                        </div>
                        <div class="actions compact">
                            <a class="button subtle" href="{{ route('admin.tables.create') }}">Add table</a>
                            <a class="button" href="{{ route('admin.areas.edit', $area) }}">Edit</a>
                            <form method="post" action="{{ route('admin.areas.toggle', $area) }}">
                                @csrf
                                @method('patch')
                                <button class="subtle" type="submit">{{ $area->is_active ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                            <form method="post" action="{{ route('admin.areas.destroy', $area) }}" data-confirm="Delete this dining area?">
                                @csrf
                                @method('delete')
                                <button class="danger" type="submit">Delete</button>
                            </form>
                        </div>
                    </div>

                    <div class="area-capacity-row">
                        <span>{{ $area->tables->count() }} tables</span>
                        <span>{{ $areaActiveTables->count() }} active</span>
                        <span>{{ $areaCapacity }} active covers</span>
                    </div>

                    @if ($areaActiveTables->isEmpty())
                        <div class="notice">
                            <strong>No active tables in this area.</strong>
                            <p style="margin-bottom: 0;">This area will not contribute capacity until at least one table is active.</p>
                        </div>
                    @endif

                    <div class="visual-table-grid">
                        @forelse ($area->tables as $table)
                            <article class="visual-table-card {{ $table->is_active ? '' : 'inactive' }} {{ $table->future_bookings_count > 0 ? 'has-bookings' : '' }}">
                                <div class="visual-table-top">
                                    <strong>{{ $table->name }}</strong>
                                    <span class="badge status-badge {{ $table->is_active ? 'green' : 'slate' }}">{{ $table->is_active ? 'Active' : 'Inactive' }}</span>
                                </div>
                                <p>{{ $table->min_covers }}-{{ $table->max_covers }} covers · {{ $table->is_joinable ? 'Joinable' : 'Standalone' }}</p>
                                @if ($table->internal_notes)
                                    <small>{{ $table->internal_notes }}</small>
                                @elseif ($table->max_covers <= 0)
                                    <small>Capacity needs review.</small>
                                @else
                                    <small>{{ $table->future_bookings_count }} upcoming booking{{ $table->future_bookings_count === 1 ? '' : 's' }}</small>
                                @endif
                                @if ($table->future_bookings_count > 0)
                                    <div class="table-warning">Future bookings assigned. Deactivate instead of deleting.</div>
                                @endif
                                @include('admin.areas.partials.table-actions', ['table' => $table])
                            </article>
                        @empty
                            <div class="empty-state compact">
                                <strong>No tables in this area.</strong>
                                <p style="margin: 0;">Add tables to make this area bookable.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    @endif
</section>
@endsection
