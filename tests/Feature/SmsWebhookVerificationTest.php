<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmsWebhookVerificationTest extends TestCase
{
    use RefreshDatabase;
    public function test_rejects_request_without_signature_when_generic_provider_configured(): void
    {
        config([
            'services.sms.webhook_provider' => 'generic',
            'services.sms.webhook_secret' => 'test-secret',
        ]);

        $response = $this->postJson('/webhook/sms/rsvp', [
            'Body' => 'yes',
            'From' => '+15551234567',
        ]);

        $response->assertForbidden();
    }

    public function test_accepts_request_with_valid_generic_hmac_signature(): void
    {
        config([
            'services.sms.webhook_provider' => 'generic',
            'services.sms.webhook_secret' => 'test-secret',
        ]);

        $payload = json_encode(['Body' => 'yes', 'From' => '+15551234567']);
        $signature = hash_hmac('sha256', $payload, 'test-secret');

        $response = $this->call(
            'POST',
            '/webhook/sms/rsvp',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_SMS_WEBHOOK_SIGNATURE' => $signature,
            ],
            $payload
        );

        // Middleware passed; controller returns 404 when no attendee matches.
        $response->assertStatus(404);
    }

    public function test_twilio_signed_request_is_verified_by_middleware(): void
    {
        config([
            'services.sms.webhook_provider' => 'twilio',
            'services.sms.twilio_auth_token' => 'twilio-auth-token',
        ]);

        $params = ['Body' => 'yes', 'From' => '+15551234567'];
        $url = rtrim((string) config('app.url'), '/').'/webhook/sms/rsvp';
        ksort($params);
        $payload = $url;
        foreach ($params as $key => $value) {
            $payload .= $key.(string) $value;
        }
        $signature = base64_encode(hash_hmac('sha1', $payload, 'twilio-auth-token', true));

        $response = $this->post($url, $params, [
            'X-Twilio-Signature' => $signature,
        ]);

        $this->assertNotEquals(403, $response->status(), 'Twilio signature should pass middleware');
        $response->assertStatus(404);
    }

    public function test_disabled_provider_allows_webhook_in_testing_environment(): void
    {
        config(['services.sms.webhook_provider' => 'disabled']);

        $response = $this->postJson('/webhook/sms/rsvp', [
            'Body' => 'yes',
            'From' => '+15559999999',
        ]);

        $response->assertStatus(404);
    }
}
