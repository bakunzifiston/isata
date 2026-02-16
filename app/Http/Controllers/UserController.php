<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403, 'No organization associated with your account.');
        }

        $users = $organization->users()->orderBy('name')->get();

        return view('users.index', [
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403, 'No organization associated with your account.');
        }

        if (! auth()->user()->isOrganizationAdmin()) {
            abort(403, 'Only organization admins can add users.');
        }

        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $organization = auth()->user()->organization;

        if (! $organization || ! auth()->user()->isOrganizationAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:admin,staff'],
        ]);

        $organization->users()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.index')
            ->with('status', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        $organization = auth()->user()->organization;

        if (! $organization || $user->organization_id !== $organization->id) {
            abort(404);
        }

        if (! auth()->user()->isOrganizationAdmin()) {
            abort(403, 'Only organization admins can edit users.');
        }

        return view('users.edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $organization = auth()->user()->organization;

        if (! $organization || $user->organization_id !== $organization->id || ! auth()->user()->isOrganizationAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'in:admin,staff'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
        ]);

        if (! empty($validated['password'])) {
            $user->update(['password' => $validated['password']]);
        }

        return redirect()->route('users.index')
            ->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $organization = auth()->user()->organization;

        if (! $organization || $user->organization_id !== $organization->id || ! auth()->user()->isOrganizationAdmin()) {
            abort(403, 'Unauthorized.');
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('status', 'User removed successfully.');
    }
}
