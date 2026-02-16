<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'date',
        'time',
        'venue',
        'meeting_link',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public function messages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function reminderSettings(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(EventReminderSettings::class);
    }

    public function rsvps(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Rsvp::class);
    }

    public function beepCalls(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BeepCall::class);
    }

    public function surveys(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Survey::class);
    }

    public function feedback(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function getDateTimeAttribute(): ?\Carbon\Carbon
    {
        if (! $this->date) {
            return null;
        }
        $time = $this->time ? substr($this->time, 0, 5) : '00:00';
        return \Carbon\Carbon::parse($this->date->format('Y-m-d') . ' ' . $time);
    }

    public function getTimeFormattedAttribute(): ?string
    {
        return $this->time ? substr($this->time, 0, 5) : null;
    }
}
