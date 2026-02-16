<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BeepCall extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_RINGING = 'ringing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'organization_id',
        'event_id',
        'attendee_id',
        'audio_file',
        'call_schedule',
        'call_status',
        'external_call_id',
        'error_message',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'call_schedule' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function attendee(): BelongsTo
    {
        return $this->belongsTo(Attendee::class);
    }

    public function getAudioUrlAttribute(): ?string
    {
        return $this->audio_file ? Storage::disk('public')->url($this->audio_file) : null;
    }

    public function getPhoneAttribute(): string
    {
        return $this->attendee?->phone ?? $this->attendee?->email ?? '';
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_QUEUED => 'Queued',
            self::STATUS_RINGING => 'Ringing',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
        ];
    }
}
