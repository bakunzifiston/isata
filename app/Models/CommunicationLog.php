<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationLog extends Model
{
    public const STATUS_SENT = 'sent';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_FAILED = 'failed';
    public const STATUS_BOUNCED = 'bounced';

    protected $fillable = [
        'organization_id',
        'event_id',
        'message_id',
        'attendee_id',
        'channel_id',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'opened_at' => 'datetime',
            'clicked_at' => 'datetime',
            'metadata' => 'array',
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

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function attendee(): BelongsTo
    {
        return $this->belongsTo(Attendee::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }
}
