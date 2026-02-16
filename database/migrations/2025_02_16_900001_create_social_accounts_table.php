<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('platform'); // facebook, linkedin, twitter, whatsapp
            $table->string('name')->nullable();
            $table->text('credentials')->nullable(); // encrypted tokens
            $table->boolean('is_active')->default(true);
            $table->string('external_id')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
