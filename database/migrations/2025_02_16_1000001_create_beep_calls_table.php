<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beep_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendee_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('audio_file');
            $table->timestamp('call_schedule');
            $table->string('call_status')->default('pending'); // pending, queued, ringing, completed, failed
            $table->string('external_call_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'call_status']);
            $table->index(['call_schedule', 'call_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beep_calls');
    }
};
