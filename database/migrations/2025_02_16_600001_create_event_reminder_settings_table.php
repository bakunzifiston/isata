<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_reminder_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reminder_24hr_template_id')->nullable()->constrained('message_templates')->nullOnDelete();
            $table->foreignId('reminder_1hr_template_id')->nullable()->constrained('message_templates')->nullOnDelete();
            $table->timestamp('sent_24hr_at')->nullable();
            $table->timestamp('sent_1hr_at')->nullable();
            $table->timestamps();

            $table->unique('event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_reminder_settings');
    }
};
