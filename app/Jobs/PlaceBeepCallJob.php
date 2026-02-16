<?php

namespace App\Jobs;

use App\Models\BeepCall;
use App\Models\OrganizationUsage;
use App\Services\TelecomApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PlaceBeepCallJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public BeepCall $beepCall
    ) {}

    public function handle(TelecomApiService $telecom): void
    {
        $call = $this->beepCall->fresh();

        if (! $call || ! in_array($call->call_status, [BeepCall::STATUS_PENDING, BeepCall::STATUS_QUEUED])) {
            return;
        }

        $success = $telecom->placeCall($call);

        if ($success && $call->call_status === BeepCall::STATUS_COMPLETED) {
            $this->incrementUsage($call->organization_id);
        }
    }

    protected function incrementUsage(int $organizationId): void
    {
        $period = now()->format('Y-m');
        $usage = OrganizationUsage::getOrCreateForPeriod($organizationId, $period);
        $usage->increment('beep_calls_count');
    }
}
