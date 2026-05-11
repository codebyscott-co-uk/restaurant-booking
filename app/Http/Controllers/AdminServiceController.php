<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminServiceController extends Controller
{
    public function index(): View
    {
        $venue = $this->currentVenue();

        return view('admin.services.index', [
            'venue' => $venue,
            'services' => $venue->services()->orderBy('starts_at')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.services.create', [
            'venue' => $this->currentVenue(),
            'service' => new Service([
                'slot_interval_minutes' => 30,
                'default_duration_minutes' => 120,
                'min_covers' => 1,
                'max_covers' => 8,
                'is_active' => true,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $venue = $this->currentVenue($request);
        $validated = $this->validateService($request);
        $validated['venue_id'] = $venue->id;
        $validated['requires_deposit'] = $request->boolean('requires_deposit');
        $validated['is_active'] = $request->boolean('is_active');

        Service::create($validated);

        return redirect()->route('admin.services.index')->with('status', 'Service created.');
    }

    public function edit(Service $service): View
    {
        $venue = $this->currentVenue();
        $this->ensureVenue($service, $venue);

        return view('admin.services.edit', [
            'venue' => $venue,
            'service' => $service,
        ]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $this->ensureVenue($service, $this->currentVenue($request));

        $validated = $this->validateService($request);
        $validated['requires_deposit'] = $request->boolean('requires_deposit');
        $validated['is_active'] = $request->boolean('is_active');

        $service->update($validated);

        return redirect()->route('admin.services.index')->with('status', 'Service updated.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $this->ensureVenue($service, $this->currentVenue());

        if ($service->bookings()->exists()) {
            return back()->withErrors(['service' => 'Services with bookings cannot be deleted. Set it inactive instead.']);
        }

        $service->delete();

        return redirect()->route('admin.services.index')->with('status', 'Service deleted.');
    }

    private function validateService(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
            'slot_interval_minutes' => ['required', 'integer', Rule::in([15, 30, 45, 60])],
            'default_duration_minutes' => ['required', 'integer', 'min:30', 'max:360'],
            'min_covers' => ['required', 'integer', 'min:1', 'max:99'],
            'max_covers' => ['required', 'integer', 'min:1', 'max:99', 'gte:min_covers'],
            'requires_deposit' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
