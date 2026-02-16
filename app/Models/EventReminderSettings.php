<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventReminderSettings extends Model
{
    protected $table = 'event_reminder_settings';

    protected $fillable = [
        'event_id',
        'reminder_24hr_template_id',
        'reminder_1hr_template_id',
        'sent_24hr_at',
        'sent_1hr_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_24hr_at' => 'datetime',
            'sent_1hr_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function template24hr(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'reminder_24hr_template_id');
    }

    public function template1hr(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'reminder_1hr_template_id');
    }
}
