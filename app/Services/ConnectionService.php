<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ConnectionService
{
    /**
     * Simulate connection status for offline queue testing.
     * When APP_SIMULATE_OFFLINE=true, treats the app as offline.
     */
    public function isOnline(): bool
    {
        return ! $this->isSimulatedOffline();
    }

    /**
     * Toggle simulated offline state at runtime (for testing).
     */
    public function setSimulatedOffline(bool $offline): void
    {
        Cache::put('app.simulate_offline', $offline, now()->addHours(24));
    }

    /**
     * Check both config and runtime cache for offline simulation.
     */
    public function isSimulatedOffline(): bool
    {
        return config('app.simulate_offline', false)
            || Cache::get('app.simulate_offline', false);
    }
}
