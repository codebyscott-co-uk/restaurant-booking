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
        $venue = $this->currentVenue($request)->load(['services', 'diningAreas.tables']);
        $date = Carbon::parse($request->query('date', today($venue->timezone)->toDateString()), $venue->timezone);
        $view = $request->query('view') === 'week' ? 'week' : 'day';
        $display = $request->query('display') === 'list' ? 'list' : 'timeline';
        $serviceId = $request->integer('service_id') ?: null;
        $status = in_array($request->query('status'), Booking::STATUSES, true) ? $request->query('status') : null;
        $search = trim((string) $request->query('search', ''));
        $services = $venue->services()->orderBy('starts_at')->get();
        $periodStart = $view === 'week' ? $date->copy()->startOfWeek() : $date->copy()->startOfDay();
        $periodEnd = $view === 'week' ? $date->copy()->endOfWeek() : $date->copy()->endOfDay();

        $bookings = Booking::with(['customer', 'service', 'tables.diningArea'])
            ->where('venue_id', $venue->id)
            ->whereBetween('starts_at', [$periodStart, $periodEnd])
            ->when($serviceId, fn ($query) => $query->where('service_id', $serviceId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('booking_reference', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($query) use ($search) {
                            $query->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('starts_at')
            ->get();

        $days = collect(range(0, $view === 'week' ? 6 : 0))
            ->map(fn ($offset) => $periodStart->copy()->addDays($offset));

        return view('admin.diary', [
            'venue' => $venue,
            'date' => $date,
            'view' => $view,
            'display' => $display,
            'serviceId' => $serviceId,
            'status' => $status,
            'search' => $search,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'days' => $days,
            'bookings' => $bookings,
            'bookingsByDay' => $bookings->groupBy(fn (Booking $booking) => $booking->starts_at->toDateString()),
            'bookingsByService' => $bookings->groupBy('service_id'),
            'services' => $services,
            'statusCounts' => $bookings->countBy('status'),
        ]);
    }
}
