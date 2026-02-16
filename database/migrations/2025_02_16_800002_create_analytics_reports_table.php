<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('period'); // e.g. 2025-02, 2025-02-16
            $table->string('period_type')->default('monthly'); // daily, monthly
            $table->unsignedInteger('messages_sent')->default(0);
            $table->decimal('delivery_rate', 5, 2)->default(0);
            $table->decimal('open_rate', 5, 2)->default(0);
            $table->decimal('rsvp_rate', 5, 2)->default(0);
            $table->decimal('attendance_rate', 5, 2)->default(0);
            $table->decimal('social_engagement', 5, 2)->default(0);
            $table->json('metrics')->nullable();
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->unique(['organization_id', 'event_id', 'period', 'period_type'], 'analytics_org_event_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_reports');
    }
};
