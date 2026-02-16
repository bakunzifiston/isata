<?php

namespace App\Services\Social;

use App\Models\SocialPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwitterService
{
    public function publish(SocialPost $post): bool
    {
        $account = $post->socialAccount;
        if (! $account || $account->platform !== 'twitter') {
            return false;
        }

        if (! config('services.twitter.api_key')) {
            Log::info('Twitter/X: Simulating post (no API key configured)', ['post_id' => $post->id]);
            $post->update(['external_post_id' => 'sim-' . $post->id, 'published_at' => now()]);
            return true;
        }

        $credentials = json_decode($account->credentials ?? '{}', true);
        $accessToken = $credentials['access_token'] ?? config('services.twitter.access_token');

        if (! $accessToken) {
            $post->update(['status' => SocialPost::STATUS_FAILED, 'error_message' => 'No access token']);
            return false;
        }

        $body = ['text' => $this->truncateText($post->content, 280)];

        $response = Http::withToken($accessToken)
            ->post('https://api.twitter.com/2/tweets', $body);

        if ($response->successful()) {
            $post->update([
                'external_post_id' => $response->json('data.id'),
                'published_at' => now(),
            ]);
            return true;
        }

        $post->update([
            'status' => SocialPost::STATUS_FAILED,
            'error_message' => $response->json('detail', $response->body()),
        ]);
        return false;
    }

    protected function truncateText(string $text, int $max): string
    {
        return strlen($text) > $max ? substr($text, 0, $max - 3) . '...' : $text;
    }

    public function fetchEngagement(SocialPost $post): void
    {
        if (! $post->external_post_id || ! config('services.twitter.api_key')) {
            return;
        }
        // Twitter API v2 engagement metrics
    }
}
