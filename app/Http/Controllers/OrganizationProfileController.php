<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOrganizationProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OrganizationProfileController extends Controller
{
    public function edit(): View
    {
        $organization = auth()->user()->organization;
        $this->authorize('view', $organization);

        return view('organization.profile', [
            'organization' => $organization->load('subscriptionPlan'),
        ]);
    }

    public function update(UpdateOrganizationProfileRequest $request): RedirectResponse
    {
        $organization = auth()->user()->organization;
        $validated = $request->validated();

        if ($request->hasFile('logo')) {
            if ($organization->logo) {
                Storage::disk('public')->delete($organization->logo);
            }
            $validated['logo'] = $request->file('logo')->store(
                'organizations/'.$organization->id,
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
