<?php

namespace App\Console\Commands;

use App\Jobs\SendMessageJob;
use App\Models\Message;
use Illuminate\Console\Command;

class ProcessScheduledMessagesCommand extends Command
{
    protected $signature = 'messages:process-scheduled';

    protected $description = 'Process scheduled messages that are due and dispatch send jobs';

    public function handle(): int
    {
        $due = Message::query()
            ->where('status', Message::STATUS_SCHEDULED)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->with(['event', 'channel'])
            ->get();

        foreach ($due as $message) {
            SendMessageJob::dispatch($message);
            $this->info("Dispatched message {$message->id} for event {$message->event->name}");
        }

        if ($due->isEmpty()) {
            $this->info('No due messages to process.');
        }

        return self::SUCCESS;
    }
}
