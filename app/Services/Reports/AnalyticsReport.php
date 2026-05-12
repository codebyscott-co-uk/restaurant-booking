<?php

namespace App\Services\Reports;

use App\Models\Booking;
use App\Models\Venue;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AnalyticsReport
{
    private const ACTIVE_STATUSES = ['pending', 'confirmed', 'seated', 'completed'];

    public function build(Venue $venue, Request $request): array
    {
        [$start, $end, $label, $preset] = $this->dateRange($request, $venue->timezone);

        $bookings = Booking::with(['customer', 'service', 'tables'])
            ->where('venue_id', $venue->id)
            ->whereBetween('starts_at', [$start, $end])
            ->orderBy('starts_at')
            ->get();

        $activeBookings = $bookings->whereIn('status', self::ACTIVE_STATUSES);
        $previousBookings = Booking::with(['service'])
            ->where('venue_id', $venue->id)
            ->whereBetween('starts_at', [$this->previousStart($start, $end), $start->subSecond()])
            ->get();

        $statusCounts = collect(Booking::STATUSES)
            ->mapWithKeys(fn (string $status) => [$status => $bookings->where('status', $status)->count()]);

        $bookingsByDay = $bookings
            ->groupBy(fn (Booking $booking) => $booking->starts_at->toDateString())
            ->map(fn (Collection $dayBookings, string $date) => [
                'label' => CarbonImmutable::parse($date)->format('D j M'),
                'bookings' => $dayBookings->count(),
                'covers' => $dayBookings->whereIn('status', self::ACTIVE_STATUSES)->sum('party_size'),
            ])
            ->values();

        $servicePerformance = $venue->services()
            ->orderBy('name')
            ->get()
            ->map(function ($service) use ($bookings) {
                $serviceBookings = $bookings->where('service_id', $service->id);
                $active = $serviceBookings->whereIn('status', self::ACTIVE_STATUSES);

                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'bookings' => $serviceBookings->count(),
                    'covers' => $active->sum('party_size'),
                    'average_party' => round($active->avg('party_size') ?: 0, 1),
                    'cancelled' => $serviceBookings->where('status', 'cancelled')->count(),
                    'no_show' => $serviceBookings->where('status', 'no_show')->count(),
                ];
            })
            ->sortByDesc('covers')
            ->values();

        $customers = $bookings->pluck('customer')->filter();
        $repeatCustomers = $customers
            ->unique('id')
            ->map(function ($customer) use ($venue) {
                $bookingCount = $customer->bookings()
                    ->where('venue_id', $venue->id)
                    ->count();

                return [
                    'name' => $customer->full_name,
                    'email' => $customer->email,
                    'bookings' => $bookingCount,
                ];
            })
            ->where('bookings', '>', 1)
            ->sortByDesc('bookings')
            ->values();

        $totalBookings = $bookings->count();
        $activeCount = $activeBookings->count();
        $cancelledCount = $statusCounts->get('cancelled', 0);
        $noShowCount = $statusCounts->get('no_show', 0);
        $totalCovers = $activeBookings->sum('party_size');
        $previousCovers = $previousBookings->whereIn('status', self::ACTIVE_STATUSES)->sum('party_size');
        $periodDays = max(1, $start->diffInDays($end) + 1);
        $tableCapacity = max(1, (int) $venue->tables()->sum('max_covers'));
        $assignedCovers = $activeBookings->filter(fn (Booking $booking) => $booking->tables->isNotEmpty())->sum('party_size');

        return [
            'range' => compact('start', 'end', 'label', 'preset'),
            'metrics' => [
                'total_bookings' => $totalBookings,
                'confirmed_bookings' => $statusCounts->get('confirmed', 0),
                'cancelled_bookings' => $cancelledCount,
                'no_show_bookings' => $noShowCount,
                'covers' => $totalCovers,
                'average_party_size' => round($activeBookings->avg('party_size') ?: 0, 1),
                'upcoming_bookings' => Booking::where('venue_id', $venue->id)
                    ->where('starts_at', '>=', now($venue->timezone))
                    ->whereNotIn('status', ['cancelled', 'no_show'])
                    ->count(),
                'cancellation_rate' => $totalBookings ? round(($cancelledCount / $totalBookings) * 100, 1) : 0,
                'no_show_rate' => $totalBookings ? round(($noShowCount / $totalBookings) * 100, 1) : 0,
                'cover_trend' => $previousCovers ? round((($totalCovers - $previousCovers) / $previousCovers) * 100, 1) : null,
                'forecast_covers' => $periodDays >= 7 ? (int) round(($totalCovers / $periodDays) * 7) : null,
                'table_utilisation' => min(100, round(($assignedCovers / ($tableCapacity * $periodDays)) * 100, 1)),
                'repeat_visit_rate' => $customers->unique('id')->count()
                    ? round(($repeatCustomers->count() / $customers->unique('id')->count()) * 100, 1)
                    : 0,
            ],
            'bookings' => $bookings,
            'bookingsByDay' => $bookingsByDay,
            'servicePerformance' => $servicePerformance,
            'statusCounts' => $statusCounts,
            'busiestDays' => $bookingsByDay->sortByDesc('covers')->take(3)->values(),
            'busiestTimes' => $activeBookings
                ->groupBy(fn (Booking $booking) => $booking->starts_at->format('H:i'))
                ->map(fn (Collection $slotBookings, string $time) => [
                    'time' => $time,
                    'bookings' => $slotBookings->count(),
                    'covers' => $slotBookings->sum('party_size'),
                ])
                ->sortByDesc('covers')
                ->take(5)
                ->values(),
            'repeatCustomers' => $repeatCustomers,
            'maxDayCovers' => max(1, (int) $bookingsByDay->max('covers')),
            'maxServiceCovers' => max(1, (int) $servicePerformance->max('covers')),
            'previous' => [
                'bookings' => $previousBookings->count(),
                'covers' => $previousCovers,
            ],
        ];
    }

    public function dateRange(Request $request, string $timezone): array
    {
        $preset = $request->string('range', 'last_30_days')->toString();
        $today = CarbonImmutable::now($timezone)->startOfDay();

        return match ($preset) {
            'today' => [$today, $today->endOfDay(), 'Today', $preset],
            'last_7_days' => [$today->subDays(6), $today->endOfDay(), 'Last 7 days', $preset],
            'this_month' => [$today->startOfMonth(), $today->endOfMonth(), 'This month', $preset],
            'last_month' => [
                $today->subMonthNoOverflow()->startOfMonth(),
                $today->subMonthNoOverflow()->endOfMonth(),
                'Last month',
                $preset,
            ],
            'custom' => [
                CarbonImmutable::parse($request->date('start_date') ?: $today->subDays(29), $timezone)->startOfDay(),
                CarbonImmutable::parse($request->date('end_date') ?: $today, $timezone)->endOfDay(),
                'Custom range',
                $preset,
            ],
            default => [$today->subDays(29), $today->endOfDay(), 'Last 30 days', 'last_30_days'],
        };
    }

    private function previousStart(CarbonImmutable $start, CarbonImmutable $end): CarbonImmutable
    {
        return $start->subDays($start->diffInDays($end) + 1);
    }
}
