<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign(['subscription_plan_id']);
        });

        if (Schema::hasTable('subscriptions')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropForeign(['subscription_plan_id']);
            });
        }

        DB::table('organizations')->update(['subscription_plan_id' => null]);
        DB::table('subscription_plans')->truncate();

        DB::table('subscription_plans')->insert([
            [
                'name' => 'Freemium',
                'slug' => 'freemium',
                'price' => 0,
                'interval' => 'monthly',
                'limits' => json_encode([
                    'events_per_month' => 1,
                    'contacts' => 50,
                    'beep_calls' => false,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'price' => 19,
                'interval' => 'monthly',
                'limits' => json_encode([
                    'events_per_month' => 5,
                    'contacts' => 300,
                    'beep_calls' => false,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'price' => 49,
                'interval' => 'monthly',
                'limits' => json_encode([
                    'events_per_month' => 20,
                    'contacts' => 2000,
                    'beep_calls' => false,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'price' => 99,
                'interval' => 'monthly',
                'limits' => json_encode([
                    'events_per_month' => null, // unlimited
                    'contacts' => null, // unlimited
                    'beep_calls' => true,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Schema::table('organizations', function (Blueprint $table) {
            $table->foreign('subscription_plan_id')->references('id')->on('subscription_plans')->nullOnDelete();
        });

        if (Schema::hasTable('subscriptions')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->foreign('subscription_plan_id')->references('id')->on('subscription_plans')->cascadeOnDelete();
            });
        }

        $freemiumId = DB::table('subscription_plans')->where('slug', 'freemium')->value('id');
        DB::table('organizations')->update(['subscription_plan_id' => $freemiumId]);
    }

    public function down(): void
    {
        // Revert would require restoring old plans - skip for simplicity
    }
};
