<?php

namespace App\Jobs;

use App\Models\SocialPost;
use App\Services\Social\FacebookService;
use App\Services\Social\LinkedInService;
use App\Services\Social\TwitterService;
use App\Services\Social\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PublishSocialPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public SocialPost $post
    ) {}

    public function handle(): void
    {
        $post = $this->post->fresh();

        if (! $post || $post->status !== SocialPost::STATUS_SCHEDULED) {
            return;
        }

        $success = match ($post->platform) {
            'facebook' => app(FacebookService::class)->publish($post),
            'linkedin' => app(LinkedInService::class)->publish($post),
            'twitter' => app(TwitterService::class)->publish($post),
            'whatsapp' => app(WhatsAppService::class)->publish($post),
            default => false,
        };

        if ($success) {
            $post->update(['status' => SocialPost::STATUS_PUBLISHED]);
        } else {
            Log::error("PublishSocialPostJob failed for post {$post->id}");
        }
    }
}
