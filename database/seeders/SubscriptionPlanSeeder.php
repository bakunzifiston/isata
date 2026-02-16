<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        SubscriptionPlan::insert([
            ['name' => 'Free', 'slug' => 'free', 'price' => 0, 'interval' => 'monthly', 'limits' => json_encode(['events' => 5, 'attendees' => 100]), 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Starter', 'slug' => 'starter', 'price' => 29, 'interval' => 'monthly', 'limits' => json_encode(['events' => 20, 'attendees' => 500]), 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pro', 'slug' => 'pro', 'price' => 79, 'interval' => 'monthly', 'limits' => json_encode(['events' => 100, 'attendees' => 5000]), 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Enterprise', 'slug' => 'enterprise', 'price' => 199, 'interval' => 'monthly', 'limits' => json_encode(['events' => null, 'attendees' => null]), 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
