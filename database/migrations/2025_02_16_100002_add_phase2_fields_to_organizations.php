<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('slug');
            $table->foreignId('subscription_plan_id')->nullable()->after('phone')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign(['subscription_plan_id']);
            $table->dropColumn(['logo', 'subscription_plan_id']);
        });
    }
};
