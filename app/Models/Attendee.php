<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendee extends Model
{
    public const RSVP_PENDING = 'pending';

    public const RSVP_CONFIRMED = 'confirmed';

    public const RSVP_DECLINED = 'declined';

    public const RSVP_ATTENDED = 'attended';

    protected $fillable = [
        'event_id',
        'contact_id',
        'rsvp_status',
    ];

    protected $with = [
        'contact',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function getNameAttribute(): ?string
    {
        return $this->contact?->name;
    }

    public function getEmailAttribute(): ?string
    {
        return $this->contact?->email;
    }

    public function getPhoneAttribute(): ?string
    {
        return $this->contact?->phone;
    }

    public function getOrganizationAttribute(): ?string
    {
        return $this->contact?->company;
    }

    public function rsvps(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Rsvp::class);
    }

    public function latestRsvp(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Rsvp::class)->latestOfMany('responded_at');
    }

    public function feedback(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public static function rsvpStatuses(): array
    {
        return [
            self::RSVP_PENDING => 'Pending',
            self::RSVP_CONFIRMED => 'Confirmed',
            self::RSVP_DECLINED => 'Declined',
            self::RSVP_ATTENDED => 'Attended',
        ];
    }
}
