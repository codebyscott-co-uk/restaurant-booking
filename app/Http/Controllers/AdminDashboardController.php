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

        $weekBookings = Booking::with(['service'])
            ->where('venue_id', $venue->id)
            ->whereBetween('starts_at', [$today->copy()->startOfDay(), $today->copy()->addDays(6)->endOfDay()])
            ->orderBy('starts_at')
            ->get();

        $upcomingBookings = Booking::with(['customer', 'service'])
            ->where('venue_id', $venue->id)
            ->where('starts_at', '>=', now($venue->timezone))
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->orderBy('starts_at')
            ->limit(5)
            ->get();

        $activeWeekBookings = $weekBookings->whereNotIn('status', ['cancelled', 'no_show']);
        $weekCovers = $activeWeekBookings->sum('party_size');
        $previousWeekCovers = Booking::where('venue_id', $venue->id)
            ->whereBetween('starts_at', [$today->copy()->subDays(7)->startOfDay(), $today->copy()->subDay()->endOfDay()])
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->sum('party_size');
        $weekCoverTrend = $previousWeekCovers > 0
            ? round((($weekCovers - $previousWeekCovers) / $previousWeekCovers) * 100)
            : ($weekCovers > 0 ? 100 : 0);
        $todayCovers = $todayBookings->whereNotIn('status', ['cancelled', 'no_show'])->sum('party_size');
        $todayTarget = max(1, (int) $venue->maximum_covers_per_slot);
        $todayCoverPercent = min(100, (int) round(($todayCovers / $todayTarget) * 100));
        $tableCapacity = max(1, (int) $venue->tables->sum('max_covers'));
        $tableLoadPercent = min(100, (int) round(($todayCovers / $tableCapacity) * 100));
        $statusCounts = $todayBookings->countBy('status');
        $serviceMix = $venue->services
            ->map(fn ($service) => [
                'name' => $service->name,
                'bookings' => $todayBookings->where('service_id', $service->id)->count(),
                'covers' => $todayBookings->where('service_id', $service->id)->whereNotIn('status', ['cancelled', 'no_show'])->sum('party_size'),
            ])
            ->sortByDesc('covers')
            ->values();
        $maxServiceCovers = max(1, (int) $serviceMix->max('covers'));
        $sourceMix = $weekBookings
            ->countBy('source')
            ->map(fn ($count, $source) => [
                'source' => ucfirst($source),
                'count' => $count,
                'percent' => max(6, min(100, (int) round(($count / max(1, $weekBookings->count())) * 100))),
            ])
            ->values();
        $hourlyFlow = $todayBookings
            ->groupBy(fn (Booking $booking) => $booking->starts_at->format('H:i'))
            ->map(fn ($bookings, $time) => [
                'time' => $time,
                'covers' => $bookings->whereNotIn('status', ['cancelled', 'no_show'])->sum('party_size'),
            ])
            ->sortBy('time')
            ->values();
        $maxHourlyCovers = max(1, (int) $hourlyFlow->max('covers'));
        $guestExperienceScore = $weekBookings->count() > 0
            ? max(0, min(100, 100 - (int) round(($weekBookings->whereIn('status', ['cancelled', 'no_show'])->count() / $weekBookings->count()) * 100)))
            : 100;

        return view('admin.dashboard', [
            'venue' => $venue,
            'today' => $today,
            'tomorrow' => $tomorrow,
            'todayBookings' => $todayBookings,
            'upcomingBookings' => $upcomingBookings,
            'statusCounts' => $statusCounts,
            'weekBookings' => $weekBookings,
            'weekCovers' => $weekCovers,
            'weekCoverTrend' => $weekCoverTrend,
            'todayCovers' => $todayCovers,
            'todayCoverPercent' => $todayCoverPercent,
            'tableCapacity' => $tableCapacity,
            'tableLoadPercent' => $tableLoadPercent,
            'serviceMix' => $serviceMix,
            'maxServiceCovers' => $maxServiceCovers,
            'sourceMix' => $sourceMix,
            'hourlyFlow' => $hourlyFlow,
            'maxHourlyCovers' => $maxHourlyCovers,
            'guestExperienceScore' => $guestExperienceScore,
            'depositTotal' => $weekBookings->sum('deposit_amount'),
        ]);
    }
}
