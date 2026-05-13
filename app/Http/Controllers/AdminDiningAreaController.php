<?php

namespace App\Http\Controllers;

use App\Models\DiningArea;
use App\Models\Booking;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDiningAreaController extends Controller
{
    public function index(Request $request): View
    {
        $venue = $this->currentVenue($request);
        $display = $request->query('display') === 'list' ? 'list' : 'grid';
        $futureBookings = fn ($query) => $query->where('starts_at', '>=', now($venue->timezone))
            ->whereNotIn('status', ['cancelled', 'no_show']);

        $areas = $venue->diningAreas()
            ->with(['tables' => fn ($query) => $query->withCount(['bookings as future_bookings_count' => $futureBookings])->orderByDesc('is_active')->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $tables = $venue->tables()
            ->with('diningArea')
            ->withCount(['bookings as future_bookings_count' => $futureBookings])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('admin.areas.index', [
            'venue' => $venue,
            'areas' => $areas,
            'tables' => $tables,
            'display' => $display,
            'summary' => [
                'areas' => $areas->count(),
                'active_tables' => $tables->where('is_active', true)->count(),
                'inactive_tables' => $tables->where('is_active', false)->count(),
                'active_capacity' => $tables->where('is_active', true)->sum('max_covers'),
                'future_bookings' => Booking::where('venue_id', $venue->id)
                    ->where('starts_at', '>=', now($venue->timezone))
                    ->whereNotIn('status', ['cancelled', 'no_show'])
                    ->count(),
            ],
            'canUseTableCombinations' => $venue->canUseFeature('advanced_booking_rules'),
            'canUseFloorplan' => $venue->canUseFeature('advanced_service_controls'),
        ]);
    }

    public function create(): View
    {
        return view('admin.areas.create', [
            'venue' => $this->currentVenue(),
            'area' => new DiningArea(['is_active' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $venue = $this->currentVenue($request);
        $validated = $this->validateArea($request);
        $validated['venue_id'] = $venue->id;
        $validated['is_active'] = $request->boolean('is_active');

        DiningArea::create($validated);

        return redirect()->route('admin.areas.index')->with('status', 'Dining area created.');
    }

    public function edit(DiningArea $area): View
    {
        $venue = $this->currentVenue();
        $this->ensureVenue($area, $venue);

        return view('admin.areas.edit', [
            'venue' => $venue,
            'area' => $area,
        ]);
    }

    public function update(Request $request, DiningArea $area): RedirectResponse
    {
        $this->ensureVenue($area, $this->currentVenue($request));

        $validated = $this->validateArea($request);
        $validated['is_active'] = $request->boolean('is_active');

        $area->update($validated);

        return redirect()->route('admin.areas.index')->with('status', 'Dining area updated.');
    }

    public function destroy(DiningArea $area): RedirectResponse
    {
        $this->ensureVenue($area, $this->currentVenue());

        if ($area->tables()->exists()) {
            return back()->withErrors(['area' => 'Dining areas with tables cannot be deleted. Move or delete the tables first.']);
        }

        $area->delete();

        return redirect()->route('admin.areas.index')->with('status', 'Dining area deleted.');
    }

    public function toggle(Request $request, DiningArea $area): RedirectResponse
    {
        $this->ensureVenue($area, $this->currentVenue($request));

        $area->update(['is_active' => ! $area->is_active]);

        return back()->with('status', $area->is_active ? 'Dining area activated.' : 'Dining area deactivated.');
    }

    private function validateArea(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
