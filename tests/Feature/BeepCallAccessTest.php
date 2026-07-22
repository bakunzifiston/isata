<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BeepCallAccessTest extends TestCase
{
    use RefreshDatabase;

    private function orgUserOnPlan(string $planSlug): User
    {
        $plan = SubscriptionPlan::updateOrCreate(
            ['slug' => $planSlug],
            [
                'name' => ucfirst($planSlug),
                'price' => $planSlug === SubscriptionPlan::SLUG_PREMIUM ? 249 : 0,
                'interval' => 'monthly',
                'limits' => [
                    'events_per_month' => 10,
                    'contacts' => 100,
                    'beep_calls' => $planSlug === SubscriptionPlan::SLUG_PREMIUM,
                ],
            ],
        );

        $org = Organization::create([
            'name' => 'Test Org',
            'slug' => 'test-org-'.Str::random(6),
            'subscription_plan_id' => $plan->id,
        ]);

        return User::create([
            'name' => 'Admin',
            'email' => 'admin-'.Str::random(6).'@example.com',
            'password' => bcrypt('password'),
            'organization_id' => $org->id,
            'role' => User::ROLE_ADMIN,
        ]);
    }

    public function test_non_premium_user_is_redirected_to_upgrade_from_beep_calls_index(): void
    {
        $user = $this->orgUserOnPlan(SubscriptionPlan::SLUG_FREEMIUM);

        $this->actingAs($user)
            ->get(route('beep-calls.index'))
            ->assertRedirect(route('subscription.upgrade', ['plan' => SubscriptionPlan::SLUG_PREMIUM]))
            ->assertSessionHas('error');
    }

    public function test_premium_user_can_open_beep_calls_index(): void
    {
        $user = $this->orgUserOnPlan(SubscriptionPlan::SLUG_PREMIUM);

        $this->actingAs($user)
            ->get(route('beep-calls.index'))
            ->assertOk();
    }
}
