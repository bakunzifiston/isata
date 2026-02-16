<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('social_account_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('platform'); // facebook, linkedin, twitter, whatsapp
            $table->text('content');
            $table->json('media_paths')->nullable();
            $table->string('status')->default('draft'); // draft, scheduled, published, failed
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('external_post_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['scheduled_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_posts');
    }
};
