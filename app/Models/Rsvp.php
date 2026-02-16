<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rsvp extends Model
{
    public const RESPONSE_YES = 'Yes';
    public const RESPONSE_NO = 'No';
    public const RESPONSE_MAYBE = 'Maybe';

    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_SMS = 'sms';
    public const CHANNEL_WEB = 'web';

    protected $fillable = [
        'attendee_id',
        'event_id',
        'response',
        'response_channel',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    public function attendee(): BelongsTo
    {
        return $this->belongsTo(Attendee::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public static function responses(): array
    {
        return [
            self::RESPONSE_YES => 'Yes',
            self::RESPONSE_NO => 'No',
            self::RESPONSE_MAYBE => 'Maybe',
        ];
    }

    public static function mapToAttendeeStatus(string $response): string
    {
        return match ($response) {
            self::RESPONSE_YES => Attendee::RSVP_CONFIRMED,
            self::RESPONSE_NO => Attendee::RSVP_DECLINED,
            self::RESPONSE_MAYBE => Attendee::RSVP_PENDING,
            default => Attendee::RSVP_PENDING,
        };
    }
}
