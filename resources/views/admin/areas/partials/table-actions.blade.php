<div class="actions compact table-actions">
    <a class="button subtle" href="{{ route('admin.tables.edit', $table) }}">Edit</a>
    <form method="post" action="{{ route('admin.tables.toggle', $table) }}">
        @csrf
        @method('patch')
        <button class="subtle" type="submit">{{ $table->is_active ? 'Deactivate' : 'Activate' }}</button>
    </form>
    @if (($table->future_bookings_count ?? 0) > 0)
        <span class="button muted-action">Delete locked</span>
    @else
        <form method="post" action="{{ route('admin.tables.destroy', $table) }}" data-confirm="Delete this table?">
            @csrf
            @method('delete')
            <button class="danger" type="submit">Delete</button>
        </form>
    @endif
</div>
