<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $venue = $this->currentVenue($request);
        $search = trim((string) $request->query('search', ''));
        $filter = $request->query('filter');
        $sort = $request->query('sort', 'last_visit');
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';

        $customers = Customer::query()
            ->forVenue($venue)
            ->with(['favouriteDiningArea', 'favouriteRestaurantTable'])
            ->withCount('bookings')
            ->withMax('bookings as last_visit_at', 'starts_at')
            ->withMin(['bookings as next_booking_at' => fn ($query) => $query->where('starts_at', '>=', now($venue->timezone))], 'starts_at')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($filter === 'vip', fn ($query) => $query->where('is_vip', true))
            ->when($filter === 'notes', fn ($query) => $query->where(function ($query) {
                $query->whereNotNull('allergies')
                    ->orWhereNotNull('dietary_requirements')
                    ->orWhereNotNull('preferences')
                    ->orWhereNotNull('notes');
            }))
            ->when($filter === 'repeat', fn ($query) => $query->has('bookings', '>', 1));

        match ($sort) {
            'name' => $customers->orderBy('first_name', $direction)->orderBy('last_name', $direction),
            'bookings' => $customers->orderBy('bookings_count', $direction),
            'next_visit' => $customers->orderBy('next_booking_at', $direction),
            default => $customers->orderBy('last_visit_at', $direction),
        };

        return view('admin.customers.index', [
            'venue' => $venue,
            'customers' => $customers->paginate(15)->withQueryString(),
            'search' => $search,
            'filter' => $filter,
            'sort' => $sort,
            'direction' => $direction,
            'summary' => [
                'total' => Customer::forVenue($venue)->count(),
                'vip' => Customer::forVenue($venue)->where('is_vip', true)->count(),
                'repeat' => Customer::forVenue($venue)->has('bookings', '>', 1)->count(),
                'with_notes' => Customer::forVenue($venue)->where(function ($query) {
                    $query->whereNotNull('allergies')
                        ->orWhereNotNull('dietary_requirements')
                        ->orWhereNotNull('preferences')
                        ->orWhereNotNull('notes');
                })->count(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $venue = $this->currentVenue($request)->load(['diningAreas.tables']);

        return view('admin.customers.create', [
            'venue' => $venue,
            'customer' => new Customer(['marketing_opt_in' => false, 'is_vip' => false]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $venue = $this->currentVenue($request);
        $validated = $this->validatedCustomer($request, $venue->id);

        $customer = Customer::create($validated + ['venue_id' => $venue->id]);

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('status', 'Customer profile created.');
    }

    public function show(Request $request, Customer $customer): View
    {
        $venue = $this->currentVenue($request)->load(['diningAreas.tables']);
        $this->ensureVenue($customer, $venue);

        $customer->load(['favouriteDiningArea', 'favouriteRestaurantTable']);

        $bookings = Booking::query()
            ->where('venue_id', $venue->id)
            ->where('customer_id', $customer->id)
            ->with(['service', 'tables.diningArea'])
            ->orderByDesc('starts_at')
            ->get();

        $activeBookings = $bookings->whereNotIn('status', ['cancelled', 'no_show']);

        return view('admin.customers.show', [
            'venue' => $venue,
            'customer' => $customer,
            'bookings' => $bookings,
            'upcomingBookings' => $bookings->where('starts_at', '>=', now($venue->timezone))->sortBy('starts_at')->values(),
            'pastBookings' => $bookings->where('starts_at', '<', now($venue->timezone))->values(),
            'metrics' => [
                'total_bookings' => $bookings->count(),
                'total_covers' => $activeBookings->sum('party_size'),
                'last_booking_date' => optional($bookings->where('starts_at', '<', now($venue->timezone))->sortByDesc('starts_at')->first())->starts_at,
                'next_booking_date' => optional($bookings->where('starts_at', '>=', now($venue->timezone))->sortBy('starts_at')->first())->starts_at,
                'cancelled_no_show_count' => $bookings->whereIn('status', ['cancelled', 'no_show'])->count(),
                'average_party_size' => $activeBookings->count() ? round($activeBookings->avg('party_size'), 1) : 0,
            ],
        ]);
    }

    public function edit(Request $request, Customer $customer): View
    {
        $venue = $this->currentVenue($request)->load(['diningAreas.tables']);
        $this->ensureVenue($customer, $venue);
        $customer->load(['favouriteDiningArea', 'favouriteRestaurantTable']);

        return view('admin.customers.edit', [
            'venue' => $venue,
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $venue = $this->currentVenue($request);
        $this->ensureVenue($customer, $venue);

        $customer->update($this->validatedCustomer($request, $venue->id));

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('status', 'Customer profile updated.');
    }

    private function validatedCustomer(Request $request, int $venueId): array
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'marketing_opt_in' => ['nullable', 'boolean'],
            'is_vip' => ['nullable', 'boolean'],
            'allergies' => ['nullable', 'string', 'max:2000'],
            'dietary_requirements' => ['nullable', 'string', 'max:2000'],
            'preferences' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:4000'],
            'favourite_dining_area_id' => ['nullable', Rule::exists('dining_areas', 'id')->where('venue_id', $venueId)],
            'favourite_restaurant_table_id' => ['nullable', Rule::exists('restaurant_tables', 'id')->where('venue_id', $venueId)],
        ]);

        $validated['marketing_opt_in'] = (bool) ($validated['marketing_opt_in'] ?? false);
        $validated['is_vip'] = (bool) ($validated['is_vip'] ?? false);

        return $validated;
    }
}
