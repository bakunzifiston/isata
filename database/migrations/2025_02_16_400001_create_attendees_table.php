<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('organization')->nullable();
            $table->string('rsvp_status')->default('pending'); // pending, confirmed, declined, attended
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendees');
    }
};
