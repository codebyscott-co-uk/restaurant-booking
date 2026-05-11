<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminStaffController extends Controller
{
    public function index(): View
    {
        $venue = $this->currentVenue();

        return view('admin.staff.index', [
            'venue' => $venue,
            'staff' => $venue->users()->orderByDesc('is_active')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.staff.create', [
            'venue' => $this->currentVenue(),
            'user' => new User(['role' => 'staff', 'is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUser($request);
        $validated['venue_id'] = $this->currentVenue($request)->id;
        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active');

        User::create($validated);

        return redirect()->route('admin.staff.index')->with('status', 'Staff user created.');
    }

    public function edit(User $user): View
    {
        $venue = $this->currentVenue();
        $this->ensureVenue($user, $venue);

        return view('admin.staff.edit', [
            'venue' => $venue,
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureVenue($user, $this->currentVenue($request));

        $validated = $this->validateUser($request, $user);

        if (($validated['is_active'] ?? false) === false && $request->user()->is($user)) {
            return back()->withErrors(['is_active' => 'You cannot deactivate your own staff account.'])->withInput();
        }

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active');
        $user->update($validated);

        return redirect()->route('admin.staff.index')->with('status', 'Staff user updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->ensureVenue($user, $this->currentVenue($request));

        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'You cannot delete your own staff account.']);
        }

        $user->delete();

        return redirect()->route('admin.staff.index')->with('status', 'Staff user deleted.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'role' => ['required', Rule::in(['owner', 'manager', 'host', 'staff'])],
            'phone' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);
    }
}
