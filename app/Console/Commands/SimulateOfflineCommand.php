<?php

namespace App\Console\Commands;

use App\Services\ConnectionService;
use Illuminate\Console\Command;

class SimulateOfflineCommand extends Command
{
    protected $signature = 'app:simulate-offline {state : on|off}';

    protected $description = 'Toggle simulated offline mode for testing the queue system (Phase 13)';

    public function handle(): int
    {
        $state = strtolower($this->argument('state'));
        $connection = app(ConnectionService::class);

        if ($state === 'on') {
            $connection->setSimulatedOffline(true);
            $this->info('Offline mode enabled. Messages will be queued until connection is restored.');
        } elseif ($state === 'off') {
            $connection->setSimulatedOffline(false);
            $this->info('Offline mode disabled. Run messages:sync-queued to process queued messages.');
        } else {
            $this->error('State must be "on" or "off".');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
