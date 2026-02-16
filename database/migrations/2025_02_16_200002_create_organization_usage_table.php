<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('period', 7); // YYYY-MM for monthly tracking
            $table->unsignedInteger('events_count')->default(0);
            $table->unsignedInteger('contacts_count')->default(0);
            $table->unsignedInteger('beep_calls_count')->default(0);
            $table->timestamps();

            $table->unique(['organization_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_usage');
    }
};
