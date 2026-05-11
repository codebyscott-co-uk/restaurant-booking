<?php

namespace App\Http\Controllers;

use App\Models\Closure;
use App\Models\OpeningHour;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AdminAvailabilityController extends Controller
{
    public function index(): View
    {
        $venue = Venue::with(['services.openingHours', 'closures.service'])->firstOrFail();

        foreach ($venue->services as $service) {
            for ($day = 0; $day <= 6; $day++) {
                OpeningHour::firstOrCreate(
                    ['service_id' => $service->id, 'day_of_week' => $day],
                    [
                        'venue_id' => $venue->id,
                        'opens_at' => $service->starts_at,
                        'closes_at' => $service->ends_at,
                        'is_closed' => in_array($day, [0, 6], true),
                    ]
                );
            }
        }

        return view('admin.availability.index', [
            'venue' => $venue->fresh(['services.openingHours', 'closures.service']),
            'dayNames' => $this->dayNames(),
        ]);
    }

    public function updateHours(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hours' => ['required', 'array'],
            'hours.*.*.id' => ['required', 'exists:opening_hours,id'],
            'hours.*.*.opens_at' => ['nullable', 'date_format:H:i'],
            'hours.*.*.closes_at' => ['nullable', 'date_format:H:i'],
            'hours.*.*.is_closed' => ['nullable', 'boolean'],
        ]);

        foreach ($validated['hours'] as $serviceHours) {
            foreach ($serviceHours as $hourData) {
                $isClosed = (bool) ($hourData['is_closed'] ?? false);

                OpeningHour::whereKey($hourData['id'])->update([
                    'opens_at' => $isClosed ? null : $hourData['opens_at'],
                    'closes_at' => $isClosed ? null : $hourData['closes_at'],
                    'is_closed' => $isClosed,
                ]);
            }
        }

        return redirect()->route('admin.availability.index')->with('status', 'Opening hours updated.');
    }

    public function storeClosure(Request $request): RedirectResponse
    {
        $venue = Venue::firstOrFail();

        $validated = $request->validate([
            'service_id' => ['nullable', 'exists:services,id'],
            'starts_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'ends_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:starts_at'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        Closure::create([
            'venue_id' => $venue->id,
            'service_id' => $validated['service_id'] ?: null,
            'starts_at' => Carbon::parse($validated['starts_at'], $venue->timezone),
            'ends_at' => Carbon::parse($validated['ends_at'], $venue->timezone),
            'reason' => $validated['reason'] ?? null,
        ]);

        return redirect()->route('admin.availability.index')->with('status', 'Closure added.');
    }

    public function destroyClosure(Closure $closure): RedirectResponse
    {
        $closure->delete();

        return redirect()->route('admin.availability.index')->with('status', 'Closure removed.');
    }

    private function dayNames(): array
    {
        return [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];
    }
}

