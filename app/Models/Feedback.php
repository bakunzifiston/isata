<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'survey_id',
        'attendee_id',
        'event_id',
        'responses',
        'submitted_at',
        'thank_you_sent_at',
        'certificate_path',
    ];

    protected function casts(): array
    {
        return [
            'responses' => 'array',
            'submitted_at' => 'datetime',
            'thank_you_sent_at' => 'datetime',
        ];
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function attendee(): BelongsTo
    {
        return $this->belongsTo(Attendee::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function getCertificateUrlAttribute(): ?string
    {
        return $this->certificate_path ? Storage::disk('public')->url($this->certificate_path) : null;
    }
}
