<?php

namespace App\Console\Commands;

use App\Jobs\SendMessageJob;
use App\Models\Message;
use App\Services\ConnectionService;
use Illuminate\Console\Command;

class ProcessQueuedMessagesCommand extends Command
{
    protected $signature = 'messages:sync-queued';

    protected $description = 'Sync queued messages when connection is restored (offline queue simulation)';

    public function handle(): int
    {
        if (! app(ConnectionService::class)->isOnline()) {
            $this->info('Connection offline. Skipping sync.');

            return self::SUCCESS;
        }

        $queued = Message::query()
            ->where('status', Message::STATUS_QUEUED)
            ->with(['event', 'channel'])
            ->get();

        foreach ($queued as $message) {
            $message->update(['status' => Message::STATUS_SCHEDULED]);
            SendMessageJob::dispatch($message);
            $this->info("Dispatched queued message {$message->id} for event {$message->event->name}");
        }

        if ($queued->isEmpty()) {
            $this->info('No queued messages to sync.');
        }

        return self::SUCCESS;
    }
}
