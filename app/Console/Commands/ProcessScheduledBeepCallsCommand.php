<?php

namespace App\Console\Commands;

use App\Jobs\PlaceBeepCallJob;
use App\Models\BeepCall;
use Illuminate\Console\Command;

class ProcessScheduledBeepCallsCommand extends Command
{
    protected $signature = 'beep-calls:process';

    protected $description = 'Process scheduled beep calls that are due';

    public function handle(): int
    {
        $due = BeepCall::query()
            ->where('call_status', BeepCall::STATUS_PENDING)
            ->where('call_schedule', '<=', now())
            ->get();

        foreach ($due as $call) {
            PlaceBeepCallJob::dispatch($call);
            $this->info("Dispatched beep call {$call->id} for attendee {$call->attendee_id}");
        }

        return self::SUCCESS;
    }
}
