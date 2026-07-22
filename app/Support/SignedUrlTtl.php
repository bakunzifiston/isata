<?php

namespace App\Support;

use Illuminate\Support\Carbon;

final class SignedUrlTtl
{
    public static function rsvpExpiresAt(): Carbon
    {
        return now()->addDays(config('signed_urls.rsvp_days', 30));
    }

    public static function feedbackExpiresAt(): Carbon
    {
        return now()->addDays(config('signed_urls.feedback_days', 90));
    }
}
