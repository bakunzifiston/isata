<?php

namespace App\Services\Social;

use App\Models\SocialAccount;
use App\Models\SocialPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookService
{
    public function publish(SocialPost $post): bool
    {
        return $this->publishToPage($post);
    }

    protected function publishToPage(SocialPost $post): bool
    {
        $account = $post->socialAccount;
        if (! $account || $account->platform !== 'facebook') {
            return false;
        }

        $credentials = json_decode($account->credentials ?? '{}', true);
        $pageAccessToken = $credentials['page_access_token'] ?? config('services.facebook.app_id');

        if (! config('services.facebook.app_id')) {
            Log::info('Facebook: Simulating post (no API key configured)', ['post_id' => $post->id]);
            $post->update(['external_post_id' => 'sim-' . $post->id, 'published_at' => now()]);
            return true;
        }

        $params = ['message' => $post->content];
        if ($post->media_paths && count($post->media_paths) > 0) {
            $params['url'] = $post->media_urls[0] ?? null;
        }

        $response = Http::post("https://graph.facebook.com/v18.0/{$account->external_id}/feed", [
            'access_token' => $pageAccessToken,
            ...$params,
        ]);

        if ($response->successful()) {
            $post->update([
                'external_post_id' => $response->json('id'),
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

    public function fetchEngagement(SocialPost $post): void
    {
        if (! $post->external_post_id) {
            return;
        }

        $account = $post->socialAccount;
        if (! $account) {
            return;
        }

        $credentials = json_decode($account->credentials ?? '{}', true);
        $token = $credentials['page_access_token'] ?? null;

        if (! $token || ! config('services.facebook.app_id')) {
            return;
        }

        $response = Http::get("https://graph.facebook.com/v18.0/{$post->external_post_id}", [
            'fields' => 'likes.summary(true),comments.summary(true),shares',
            'access_token' => $token,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $this->updateEngagement($post, [
                'like' => $data['likes']['summary']['total_count'] ?? 0,
                'comment' => $data['comments']['summary']['total_count'] ?? 0,
                'share' => $data['shares']['count'] ?? 0,
            ]);
        }
    }

    protected function updateEngagement(SocialPost $post, array $counts): void
    {
        foreach ($counts as $type => $count) {
            $post->engagement()->updateOrCreate(
                ['engagement_type' => $type],
                ['count' => $count, 'platform' => 'facebook', 'fetched_at' => now()]
            );
        }
    }
}
