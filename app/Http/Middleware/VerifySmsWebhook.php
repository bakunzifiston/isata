<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifySmsWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $provider = config('services.sms.webhook_provider', 'generic');

        if ($provider === 'disabled') {
            if (! app()->environment('local', 'testing')) {
                Log::warning('SMS webhook verification disabled outside local/testing — request rejected.', [
                    'ip' => $request->ip(),
                ]);

                return response('Webhook verification not configured.', Response::HTTP_FORBIDDEN);
            }

            Log::debug('SMS webhook verification skipped (disabled, local/testing).', [
                'ip' => $request->ip(),
            ]);

            return $next($request);
        }

        $valid = match ($provider) {
            'twilio' => $this->verifyTwilio($request),
            'generic' => $this->verifyGenericHmac($request),
            default => false,
        };

        if (! $valid) {
            Log::warning('SMS webhook signature verification failed.', [
                'ip' => $request->ip(),
                'provider' => $provider,
                'user_agent' => $request->userAgent(),
            ]);

            return response('Invalid webhook signature.', Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }

    protected function verifyTwilio(Request $request): bool
    {
        $authToken = config('services.sms.twilio_auth_token');
        $signature = $request->header('X-Twilio-Signature');

        if (! is_string($authToken) || $authToken === '' || ! is_string($signature) || $signature === '') {
            return false;
        }

        $data = $request->post();
        ksort($data);

        $payload = $request->fullUrl();
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            $payload .= $key.(string) $value;
        }

        $expected = base64_encode(hash_hmac('sha1', $payload, $authToken, true));

        return hash_equals($expected, $signature);
    }

    protected function verifyGenericHmac(Request $request): bool
    {
        $secret = config('services.sms.webhook_secret');

        if (! is_string($secret) || $secret === '') {
            return false;
        }

        $signature = $request->header('X-SMS-Webhook-Signature')
            ?? $request->header('X-Webhook-Signature');

        if (! is_string($signature) || $signature === '') {
            return false;
        }

        $computed = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($computed, $signature);
    }
}
