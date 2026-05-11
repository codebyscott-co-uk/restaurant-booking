<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AdminDiaryController extends Controller
{
    public function __invoke(Request $request): View
    {
        $venue = Venue::with(['services', 'diningAreas.tables'])->firstOrFail();
        $date = Carbon::parse($request->query('date', today($venue->timezone)->toDateString()), $venue->timezone);

        $bookings = Booking::with(['customer', 'service', 'tables.diningArea'])
            ->where('venue_id', $venue->id)
            ->whereBetween('starts_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
            ->orderBy('starts_at')
            ->get();

        return view('admin.diary', [
            'venue' => $venue,
            'date' => $date,
            'bookings' => $bookings,
            'services' => $venue->services()->orderBy('starts_at')->get(),
            'statusCounts' => $bookings->countBy('status'),
        ]);
    }
}
