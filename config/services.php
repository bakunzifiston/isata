<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'facebook' => [
        'app_id' => env('FACEBOOK_APP_ID'),
        'app_secret' => env('FACEBOOK_APP_SECRET'),
    ],

    'linkedin' => [
        'client_id' => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
    ],

    'twitter' => [
        'api_key' => env('TWITTER_API_KEY'),
        'api_secret' => env('TWITTER_API_SECRET'),
        'access_token' => env('TWITTER_ACCESS_TOKEN'),
        'access_secret' => env('TWITTER_ACCESS_SECRET'),
    ],

    'whatsapp' => [
        'api_key' => env('WHATSAPP_API_KEY'),
        'phone_id' => env('WHATSAPP_PHONE_ID'),
    ],

    'telecom' => [
        'api_key' => env('TELECOM_API_KEY'),
        'api_url' => env('TELECOM_API_URL', 'https://api.telecom.example.com'),
    ],

  /*
  |--------------------------------------------------------------------------
  | SMS webhook (inbound RSVP replies)
  |--------------------------------------------------------------------------
  |
  | webhook_provider: twilio | generic | disabled
  |   - twilio: validates X-Twilio-Signature using TWILIO_AUTH_TOKEN
  |   - generic: validates X-SMS-Webhook-Signature (HMAC-SHA256 of raw body)
  |   - disabled: only allowed in local/testing (rejected in production)
  |
  */
    'sms' => [
        'webhook_provider' => env('SMS_WEBHOOK_PROVIDER', 'generic'),
        'webhook_secret' => env('SMS_WEBHOOK_SECRET'),
        'twilio_auth_token' => env('TWILIO_AUTH_TOKEN'),
    ],

];
