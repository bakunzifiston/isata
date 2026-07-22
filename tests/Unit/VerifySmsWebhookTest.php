<?php

namespace Tests\Unit;

use App\Http\Middleware\VerifySmsWebhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class VerifySmsWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_twilio_signature_validation_accepts_valid_request(): void
    {
        config([
            'services.sms.webhook_provider' => 'twilio',
            'services.sms.twilio_auth_token' => 'twilio-auth-token',
        ]);

        $params = ['Body' => 'yes', 'From' => '+15551234567'];
        $url = 'http://localhost/webhook/sms/rsvp';
        ksort($params);
        $payload = $url;
        foreach ($params as $key => $value) {
            $payload .= $key.(string) $value;
        }
        $signature = base64_encode(hash_hmac('sha1', $payload, 'twilio-auth-token', true));

        $request = Request::create($url, 'POST', $params);
        $request->headers->set('X-Twilio-Signature', $signature);

        $middleware = new VerifySmsWebhook;
        $passed = false;
        $middleware->handle($request, function () use (&$passed) {
            $passed = true;

            return response('ok');
        });

        $this->assertTrue($passed);
    }
}
