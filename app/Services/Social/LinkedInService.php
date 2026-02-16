<?php

namespace App\Services\Social;

use App\Models\SocialPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LinkedInService
{
    public function publish(SocialPost $post): bool
    {
        $account = $post->socialAccount;
        if (! $account || $account->platform !== 'linkedin') {
            return false;
        }

        if (! config('services.linkedin.client_id')) {
            Log::info('LinkedIn: Simulating post (no API key configured)', ['post_id' => $post->id]);
            $post->update(['external_post_id' => 'sim-' . $post->id, 'published_at' => now()]);
            return true;
        }

        $credentials = json_decode($account->credentials ?? '{}', true);
        $accessToken = $credentials['access_token'] ?? null;

        if (! $accessToken) {
            $post->update(['status' => SocialPost::STATUS_FAILED, 'error_message' => 'No access token']);
            return false;
        }

        $body = [
            'author' => 'urn:li:person:' . ($account->external_id ?? ''),
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => [
                    'shareCommentary' => ['text' => $post->content],
                    'shareMediaCategory' => 'NONE',
                ],
            ],
        ];

        $response = Http::withToken($accessToken)
            ->post('https://api.linkedin.com/v2/ugcPosts', $body);

        if ($response->successful()) {
            $post->update([
                'external_post_id' => $response->header('X-RestLi-Id') ?: $response->json('id'),
                'published_at' => now(),
            ]);
            return true;
        }

        $post->update([
            'status' => SocialPost::STATUS_FAILED,
            'error_message' => $response->json('message', $response->body()),
        ]);
        return false;
    }

    public function fetchEngagement(SocialPost $post): void
    {
        if (! $post->external_post_id || ! config('services.linkedin.client_id')) {
            return;
        }
        // LinkedIn engagement API requires separate endpoints
    }
}
