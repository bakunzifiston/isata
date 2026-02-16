<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->timestamp('sent_at');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->string('status')->default('sent'); // sent, delivered, failed, bounced
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'sent_at']);
            $table->index(['event_id', 'channel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
    }
};
