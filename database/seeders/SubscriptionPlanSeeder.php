<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Mirrors database/migrations/2025_02_16_200003_refresh_subscription_plans_for_phase3.php
     * so fresh seeds cannot reintroduce stale plan slugs or limit keys.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Freemium',
                'slug' => SubscriptionPlan::SLUG_FREEMIUM,
                'price' => 0,
                'interval' => 'monthly',
                'limits' => [
                    'events_per_month' => 1,
                    'contacts' => 50,
                    'beep_calls' => false,
                ],
            ],
            [
                'name' => 'Basic',
                'slug' => SubscriptionPlan::SLUG_BASIC,
                'price' => 19,
                'interval' => 'monthly',
                'limits' => [
                    'events_per_month' => 5,
                    'contacts' => 300,
                    'beep_calls' => false,
                ],
            ],
            [
                'name' => 'Pro',
                'slug' => SubscriptionPlan::SLUG_PRO,
                'price' => 49,
                'interval' => 'monthly',
                'limits' => [
                    'events_per_month' => 20,
                    'contacts' => 2000,
                    'beep_calls' => false,
                ],
            ],
            [
                'name' => 'Premium',
                'slug' => SubscriptionPlan::SLUG_PREMIUM,
                'price' => 99,
                'interval' => 'monthly',
                'limits' => [
                    'events_per_month' => null,
                    'contacts' => null,
                    'beep_calls' => true,
                ],
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                [
                    'name' => $plan['name'],
                    'price' => $plan['price'],
                    'interval' => $plan['interval'],
                    'limits' => $plan['limits'],
                ]
            );
        }
    }
}
