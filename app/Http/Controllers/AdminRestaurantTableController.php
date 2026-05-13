<?php

namespace App\Http\Controllers;

use App\Models\DiningArea;
use App\Models\RestaurantTable;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminRestaurantTableController extends Controller
{
    public function create(): View
    {
        $venue = $this->currentVenue();

        return view('admin.tables.create', [
            'venue' => $venue,
            'areas' => $venue->diningAreas()->where('is_active', true)->get(),
            'table' => new RestaurantTable([
                'min_covers' => 1,
                'max_covers' => 2,
                'is_joinable' => false,
                'is_active' => true,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $venue = $this->currentVenue($request);
        $validated = $this->validateTable($request);
        abort_unless($venue->diningAreas()->whereKey($validated['dining_area_id'])->exists(), 404);
        $validated['venue_id'] = $venue->id;
        $validated['is_joinable'] = $request->boolean('is_joinable');
        $validated['is_active'] = $request->boolean('is_active');

        RestaurantTable::create($validated);

        return redirect()->route('admin.areas.index')->with('status', 'Table created.');
    }

    public function edit(RestaurantTable $table): View
    {
        $venue = $this->currentVenue();
        $this->ensureVenue($table, $venue);

        return view('admin.tables.edit', [
            'venue' => $venue,
            'areas' => $venue->diningAreas()->where('is_active', true)->get(),
            'table' => $table,
        ]);
    }

    public function update(Request $request, RestaurantTable $table): RedirectResponse
    {
        $venue = $this->currentVenue($request);
        $this->ensureVenue($table, $venue);
        $validated = $this->validateTable($request);
        abort_unless($venue->diningAreas()->whereKey($validated['dining_area_id'])->exists(), 404);
        $validated['is_joinable'] = $request->boolean('is_joinable');
        $validated['is_active'] = $request->boolean('is_active');

        $table->update($validated);

        return redirect()->route('admin.areas.index')->with('status', 'Table updated.');
    }

    public function destroy(RestaurantTable $table): RedirectResponse
    {
        $this->ensureVenue($table, $this->currentVenue());

        if ($table->bookings()->where('starts_at', '>=', now())->whereNotIn('status', ['cancelled', 'no_show'])->exists()) {
            return back()->withErrors(['table' => 'This table has future bookings. Deactivate it instead so existing bookings stay intact.']);
        }

        $table->delete();

        return redirect()->route('admin.areas.index')->with('status', 'Table deleted.');
    }

    public function toggle(Request $request, RestaurantTable $table): RedirectResponse
    {
        $this->ensureVenue($table, $this->currentVenue($request));

        $table->update(['is_active' => ! $table->is_active]);

        return back()->with('status', $table->is_active ? 'Table activated.' : 'Table deactivated.');
    }

    private function validateTable(Request $request): array
    {
        $venue = $this->currentVenue($request);

        return $request->validate([
            'dining_area_id' => ['required', Rule::exists('dining_areas', 'id')->where('venue_id', $venue->id)],
            'name' => ['required', 'string', 'max:255'],
            'min_covers' => ['required', 'integer', 'min:1', 'max:99'],
            'max_covers' => ['required', 'integer', 'min:1', 'max:99', 'gte:min_covers'],
            'internal_notes' => ['nullable', 'string', 'max:1000'],
            'is_joinable' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
