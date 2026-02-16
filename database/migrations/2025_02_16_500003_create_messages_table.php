<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->string('subject')->nullable();
            $table->text('content');
            $table->string('audio_file')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->string('status')->default('draft'); // draft, scheduled, sent, failed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
