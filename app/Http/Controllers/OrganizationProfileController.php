<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OrganizationProfileController extends Controller
{
    public function edit(): View
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403, 'No organization associated with your account.');
        }

        return view('organization.profile', [
            'organization' => $organization->load('subscriptionPlan'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403, 'No organization associated with your account.');
        }

        if (! auth()->user()->isOrganizationAdmin()) {
            abort(403, 'Only organization admins can update the profile.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'max:2048'], // 2MB
        ]);

        if ($request->hasFile('logo')) {
            if ($organization->logo) {
                Storage::disk('public')->delete($organization->logo);
            }
            $validated['logo'] = $request->file('logo')->store(
                'organizations/' . $organization->id,
                'public'
            );
        }

        $organization->update([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'logo' => $validated['logo'] ?? $organization->logo,
        ]);

        return redirect()->route('organization.profile.edit')
            ->with('status', 'Organization profile updated successfully.');
    }
}
