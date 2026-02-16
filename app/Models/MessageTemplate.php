<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageTemplate extends Model
{
    protected $fillable = [
        'organization_id',
        'channel_id',
        'name',
        'subject',
        'content',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public static function personalizationTags(): array
    {
        return [
            '{name}' => 'Attendee name',
            '{event_name}' => 'Event name',
            '{event_time}' => 'Event date and time',
            '{venue}' => 'Event venue',
            '{meeting_link}' => 'Meeting link',
            '{rsvp_link}' => 'RSVP response link',
            '{feedback_link}' => 'Feedback survey link',
        ];
    }

    public function renderPreview(Event $event, ?Attendee $attendee = null): string
    {
        $content = $this->content;
        $content = str_replace('{event_name}', $event->name ?? '', $content);
        $content = str_replace('{event_time}', $event->date?->format('M j, Y') . ($event->time_formatted ? ' at ' . $event->time_formatted : ''), $content);
        $content = str_replace('{venue}', $event->venue ?? '', $content);
        $content = str_replace('{meeting_link}', $event->meeting_link ?? '', $content);
        $content = str_replace('{name}', $attendee?->name ?? 'Guest', $content);
        $content = str_replace('{rsvp_link}', $attendee ? \Illuminate\Support\Facades\URL::signedRoute('rsvp.show', ['event' => $event, 'attendee' => $attendee], now()->addDays(30)) : '', $content);
        $content = str_replace('{feedback_link}', $attendee ? \Illuminate\Support\Facades\URL::signedRoute('feedback.show', ['event' => $event, 'attendee' => $attendee], now()->addDays(90)) : '', $content);

        return $content;
    }
}
