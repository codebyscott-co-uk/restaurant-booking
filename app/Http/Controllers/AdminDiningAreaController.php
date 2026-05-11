<?php

namespace App\Http\Controllers;

use App\Models\DiningArea;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDiningAreaController extends Controller
{
    public function index(): View
    {
        $venue = Venue::with(['diningAreas.tables'])->firstOrFail();

        return view('admin.areas.index', [
            'venue' => $venue,
            'areas' => $venue->diningAreas,
            'tables' => $venue->tables()->with('diningArea')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.areas.create', [
            'venue' => Venue::firstOrFail(),
            'area' => new DiningArea(['is_active' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $venue = Venue::firstOrFail();
        $validated = $this->validateArea($request);
        $validated['venue_id'] = $venue->id;
        $validated['is_active'] = $request->boolean('is_active');

        DiningArea::create($validated);

        return redirect()->route('admin.areas.index')->with('status', 'Dining area created.');
    }

    public function edit(DiningArea $area): View
    {
        return view('admin.areas.edit', [
            'venue' => Venue::firstOrFail(),
            'area' => $area,
        ]);
    }

    public function update(Request $request, DiningArea $area): RedirectResponse
    {
        $validated = $this->validateArea($request);
        $validated['is_active'] = $request->boolean('is_active');

        $area->update($validated);

        return redirect()->route('admin.areas.index')->with('status', 'Dining area updated.');
    }

    public function destroy(DiningArea $area): RedirectResponse
    {
        if ($area->tables()->exists()) {
            return back()->withErrors(['area' => 'Dining areas with tables cannot be deleted. Move or delete the tables first.']);
        }

        $area->delete();

        return redirect()->route('admin.areas.index')->with('status', 'Dining area deleted.');
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

