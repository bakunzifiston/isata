<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_engagement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_post_id')->constrained()->cascadeOnDelete();
            $table->string('platform');
            $table->string('engagement_type'); // like, share, comment, reply, view
            $table->unsignedInteger('count')->default(0);
            $table->string('external_id')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->unique(['social_post_id', 'engagement_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_engagement');
    }
};
