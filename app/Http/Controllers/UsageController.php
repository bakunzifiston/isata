<?php

namespace App\Http\Controllers;

use App\Models\OrganizationUsage;
use Illuminate\View\View;

class UsageController extends Controller
{
    public function index(): View
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403, 'No organization associated with your account.');
        }

        $plan = $organization->subscriptionPlan;
        $currentPeriod = now()->format('Y-m');
        $usage = OrganizationUsage::getOrCreateForPeriod($organization->id, $currentPeriod);

        $eventsLimit = $plan?->getEventsLimitAttribute();
        $contactsLimit = $plan?->getContactsLimitAttribute();
        $hasBeepCalls = $plan?->hasBeepCalls() ?? false;

        return view('usage.index', [
            'organization' => $organization,
            'plan' => $plan,
            'usage' => $usage,
            'currentPeriod' => $currentPeriod,
            'eventsLimit' => $eventsLimit,
            'contactsLimit' => $contactsLimit,
            'hasBeepCalls' => $hasBeepCalls,
        ]);
    }
}
