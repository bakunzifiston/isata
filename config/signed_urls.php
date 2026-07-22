<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Signed URL time-to-live (days)
    |--------------------------------------------------------------------------
    |
    | These URLs grant access to attendee PII (name, email context) without
    | authentication. Shorter TTLs reduce exposure if a link is forwarded or
    | leaked. Adjust only with product/security review.
    |
    */

    // RSVP respond links: covers typical pre-event reminder cycles (up to ~4
    // weeks before the event) without keeping PII-bearing links valid indefinitely.
    'rsvp_days' => (int) env('SIGNED_URL_RSVP_DAYS', 30),

    // Post-event feedback surveys: attendees may respond days or weeks after the
    // event; 90 days allows follow-up campaigns without permanent open access.
    'feedback_days' => (int) env('SIGNED_URL_FEEDBACK_DAYS', 90),

];
