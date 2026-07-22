<?php

namespace App\Console\Commands;

use App\Jobs\PublishSocialPostJob;
use App\Models\Channel;
use App\Models\Message;
use Illuminate\Console\Command;

class ProcessScheduledSocialPostsCommand extends Command
{
    protected $signature = 'social:process-scheduled';

    protected $description = 'Publish scheduled social posts that are due';

    public function handle(): int
    {
        $channelId = Channel::where('slug', Channel::SLUG_SOCIAL_MEDIA)->value('id');

        if (! $channelId) {
            return self::SUCCESS;
        }

        $due = Message::query()
            ->where('channel_id', $channelId)
            ->where('status', Message::STATUS_SCHEDULED)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($due as $message) {
            PublishSocialPostJob::dispatch($message);
            $this->info("Dispatched social message {$message->id} for {$message->social_platform}");
        }

        return self::SUCCESS;
    }
}
