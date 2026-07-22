<?php

namespace App\Jobs;

use App\Models\Message;
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
        public Message $message
    ) {}

    public function handle(): void
    {
        $message = $this->message->fresh(['channel']);

        if (! $message || ! $message->isSocialMessage() || $message->status !== Message::STATUS_SCHEDULED) {
            return;
        }

        $success = match ($message->social_platform) {
            'facebook' => app(FacebookService::class)->publish($message),
            'linkedin' => app(LinkedInService::class)->publish($message),
            'twitter' => app(TwitterService::class)->publish($message),
            'whatsapp' => app(WhatsAppService::class)->publish($message),
            default => false,
        };

        if ($success) {
            $message->update([
                'status' => Message::STATUS_SENT,
                'published_at' => now(),
            ]);
        } else {
            Log::error("PublishSocialPostJob failed for message {$message->id}");
        }
    }
}
