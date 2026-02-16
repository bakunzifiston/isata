<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('response'); // Yes, No, Maybe
            $table->string('response_channel')->nullable(); // email, sms, web
            $table->timestamp('responded_at');
            $table->timestamps();

            $table->index(['event_id', 'response']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rsvps');
    }
};
