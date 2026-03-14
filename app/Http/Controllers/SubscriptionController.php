<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function plans(): View
    {
        if (! Schema::hasTable('subscription_plans')) {
            return view('subscription.plans', [
                'plans' => collect(),
                'currentPlan' => null,
            ]);
        }

        $plans = SubscriptionPlan::orderBy('price')->get();
        $currentPlan = auth()->user()->organization?->subscriptionPlan;

        return view('subscription.plans', [
            'plans' => $plans,
            'currentPlan' => $currentPlan,
        ]);
    }

    public function upgrade(\Illuminate\Http\Request $request): View
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403, 'No organization associated with your account.');
        }

        if (! Schema::hasTable('subscription_plans')) {
            return view('subscription.upgrade', [
                'plans' => collect(),
                'currentPlan' => null,
                'organization' => $organization,
                'preselectedSlug' => $request->query('plan'),
            ]);
        }

        $plans = SubscriptionPlan::where('price', '>', 0)->orderBy('price')->get();
        $currentPlan = $organization->subscriptionPlan;
        $preselectedSlug = $request->query('plan');

        return view('subscription.upgrade', [
            'plans' => $plans,
            'currentPlan' => $currentPlan,
            'organization' => $organization,
            'preselectedSlug' => $preselectedSlug,
        ]);
    }

    public function storeUpgrade(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        $organization = auth()->user()->organization;

        if (! $organization || ! auth()->user()->isOrganizationAdmin()) {
            abort(403, 'Unauthorized.');
        }

        if (! Schema::hasTable('subscription_plans')) {
            return redirect()->route('subscription.plans')
                ->with('error', 'Subscription plans are not available yet.');
        }

        $plan = SubscriptionPlan::findOrFail($request->input('plan_id'));

        if ($plan->id === $organization->subscription_plan_id) {
            return redirect()->route('subscription.plans')->with('status', 'You are already on this plan.');
        }

        $organization->update(['subscription_plan_id' => $plan->id]);

        return redirect()->route('subscription.plans')
            ->with('status', 'Subscription updated to ' . $plan->name . '.');
    }
}
