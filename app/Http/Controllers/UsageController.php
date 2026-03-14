<?php

namespace App\Http\Controllers;

use App\Models\OrganizationUsage;
use Illuminate\Support\Facades\Schema;
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

        if (! Schema::hasTable('organization_usage')) {
            $usage = (object) [
                'events_count' => 0,
                'contacts_count' => 0,
                'beep_calls_count' => 0,
            ];
        } else {
            $usage = OrganizationUsage::getOrCreateForPeriod($organization->id, $currentPeriod);
        }

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
