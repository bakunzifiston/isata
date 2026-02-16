<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsReport extends Model
{
    protected $fillable = [
        'organization_id',
        'event_id',
        'period',
        'period_type',
        'messages_sent',
        'delivery_rate',
        'open_rate',
        'rsvp_rate',
        'attendance_rate',
        'social_engagement',
        'metrics',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'metrics' => 'array',
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
}
