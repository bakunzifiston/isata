<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterOrganizationController extends Controller
{
    public function create(): View
    {
        return view('auth.register-organization');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $slug = Str::slug($validated['organization_name']);
        $baseSlug = $slug;
        $counter = 1;

        while (Organization::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        DB::transaction(function () use ($validated, $slug) {
            $freemiumPlan = null;
            if (Schema::hasTable('subscription_plans')) {
                $freemiumPlan = SubscriptionPlan::where('slug', 'freemium')->first();
            }
            $orgData = [
                'name' => $validated['organization_name'],
                'slug' => $slug,
            ];
            if (Schema::hasColumn('organizations', 'subscription_plan_id')) {
                $orgData['subscription_plan_id'] = $freemiumPlan?->id;
            }
            $organization = Organization::create($orgData);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'organization_id' => $organization->id,
                'role' => User::ROLE_ADMIN,
            ]);

            Auth::login($user);
        });

        return redirect()->route('dashboard');
    }
}
