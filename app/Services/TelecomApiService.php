<?php

namespace App\Services;

use App\Models\BeepCall;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Telecom API integration placeholder.
 * Replace with actual provider (Twilio, Plivo, Exotel, etc.) when integrating.
 */
class TelecomApiService
{
    protected ?string $apiKey;

    protected ?string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.telecom.api_key');
        $this->apiUrl = config('services.telecom.api_url', 'https://api.telecom.example.com');
    }

    public function placeCall(BeepCall $call): bool
    {
        $phone = $call->phone;
        $audioUrl = $call->audio_file
            ? \Illuminate\Support\Facades\url(\Illuminate\Support\Facades\Storage::disk('public')->url($call->audio_file))
            : null;

        if (! $phone || ! $audioUrl) {
            $call->update([
                'call_status' => BeepCall::STATUS_FAILED,
                'error_message' => 'Missing phone number or audio file',
            ]);
            return false;
        }

        if (! $this->apiKey) {
            Log::info('Telecom API: Simulating beep call (no API key configured)', [
                'call_id' => $call->id,
                'phone' => $phone,
            ]);
            $call->update([
                'call_status' => BeepCall::STATUS_COMPLETED,
                'external_call_id' => 'sim-' . $call->id,
                'completed_at' => now(),
            ]);
            return true;
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->post($this->apiUrl . '/calls', [
                    'to' => $this->normalizePhone($phone),
                    'audio_url' => $audioUrl,
                    'timeout' => 30,
                ]);

            if ($response->successful()) {
                $call->update([
                    'call_status' => BeepCall::STATUS_QUEUED,
                    'external_call_id' => $response->json('call_id'),
                ]);
                return true;
            }

            $call->update([
                'call_status' => BeepCall::STATUS_FAILED,
                'error_message' => $response->json('message', $response->body()),
            ]);
            return false;
        } catch (\Throwable $e) {
            $call->update([
                'call_status' => BeepCall::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);
            Log::error('Telecom API error: ' . $e->getMessage());
            return false;
        }
    }

    protected function normalizePhone(string $phone): string
    {
        return preg_replace('/\D/', '', $phone);
    }
}
