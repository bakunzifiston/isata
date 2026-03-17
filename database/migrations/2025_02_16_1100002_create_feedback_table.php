<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('feedback')) {
            return;
        }

        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->json('responses'); // {question_id: answer}
            $table->timestamp('submitted_at');
            $table->timestamp('thank_you_sent_at')->nullable();
            $table->string('certificate_path')->nullable();
            $table->timestamps();

            $table->unique(['survey_id', 'attendee_id']);
            $table->index(['event_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
