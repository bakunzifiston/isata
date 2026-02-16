<?php

namespace App\Services\Social;

use App\Models\SocialPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Business API integration (future-ready).
 * Requires WhatsApp Business API access and approval.
 */
class WhatsAppService
{
    public function publish(SocialPost $post): bool
    {
        if (! config('services.whatsapp.api_key')) {
            Log::info('WhatsApp Business: Simulating post (no API key configured)', ['post_id' => $post->id]);
            $post->update(['external_post_id' => 'sim-' . $post->id, 'published_at' => now()]);
            return true;
        }

        $account = $post->socialAccount;
        if (! $account || $account->platform !== 'whatsapp') {
            return false;
        }

        $credentials = json_decode($account->credentials ?? '{}', true);
        $phoneId = $credentials['phone_id'] ?? config('services.whatsapp.phone_id');

        if (! $phoneId) {
            $post->update(['status' => SocialPost::STATUS_FAILED, 'error_message' => 'No phone ID']);
            return false;
        }

        // WhatsApp Cloud API - send template or text message
        $response = Http::withToken(config('services.whatsapp.api_key'))
            ->post("https://graph.facebook.com/v18.0/{$phoneId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $credentials['to'] ?? '',
                'type' => 'text',
                'text' => ['body' => $this->truncateText($post->content, 4096)],
            ]);

        if ($response->successful()) {
            $post->update([
                'external_post_id' => $response->json('messages.0.id'),
                'published_at' => now(),
            ]);
            return true;
        }

        $post->update([
            'status' => SocialPost::STATUS_FAILED,
            'error_message' => $response->json('error.message', $response->body()),
        ]);
        return false;
    }

    protected function truncateText(string $text, int $max): string
    {
        return strlen($text) > $max ? substr($text, 0, $max - 3) . '...' : $text;
    }
}
