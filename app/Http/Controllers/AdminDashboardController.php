<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Venue;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        $venue = $this->currentVenue()->load(['services', 'diningAreas.tables', 'tables']);
        $today = today($venue->timezone);
        $tomorrow = $today->copy()->addDay();

        $todayBookings = Booking::with(['customer', 'service', 'tables'])
            ->where('venue_id', $venue->id)
            ->whereBetween('starts_at', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->orderBy('starts_at')
            ->get();

        $upcomingBookings = Booking::with(['customer', 'service'])
            ->where('venue_id', $venue->id)
            ->where('starts_at', '>=', now($venue->timezone))
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->orderBy('starts_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'venue' => $venue,
            'today' => $today,
            'tomorrow' => $tomorrow,
            'todayBookings' => $todayBookings,
            'upcomingBookings' => $upcomingBookings,
            'statusCounts' => $todayBookings->countBy('status'),
            'weekCovers' => Booking::where('venue_id', $venue->id)
                ->whereBetween('starts_at', [$today->copy()->startOfDay(), $today->copy()->addDays(6)->endOfDay()])
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->sum('party_size'),
        ]);
    }
}
