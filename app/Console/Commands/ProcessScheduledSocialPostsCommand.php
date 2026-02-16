<?php

namespace App\Console\Commands;

use App\Jobs\PublishSocialPostJob;
use App\Models\SocialPost;
use Illuminate\Console\Command;

class ProcessScheduledSocialPostsCommand extends Command
{
    protected $signature = 'social:process-scheduled';

    protected $description = 'Publish scheduled social posts that are due';

    public function handle(): int
    {
        $due = SocialPost::query()
            ->where('status', SocialPost::STATUS_SCHEDULED)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($due as $post) {
            PublishSocialPostJob::dispatch($post);
            $this->info("Dispatched post {$post->id} for {$post->platform}");
        }

        return self::SUCCESS;
    }
}
