<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Message extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'event_id',
        'channel_id',
        'subject',
        'content',
        'audio_file',
        'scheduled_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function getAudioUrlAttribute(): ?string
    {
        return $this->audio_file ? Storage::disk('public')->url($this->audio_file) : null;
    }

    public function renderContentForAttendee(Attendee $attendee): string
    {
        $content = $this->content;
        $event = $this->event;

        $content = str_replace('{name}', $attendee->name, $content);
        $content = str_replace('{event_name}', $event->name ?? '', $content);
        $content = str_replace('{event_time}', $event->date?->format('M j, Y') . ($event->time_formatted ? ' at ' . $event->time_formatted : ''), $content);
        $content = str_replace('{venue}', $event->venue ?? '', $content);
        $content = str_replace('{meeting_link}', $event->meeting_link ?? '', $content);
        $content = str_replace('{rsvp_link}', $this->getRsvpLinkForAttendee($attendee), $content);
        $content = str_replace('{feedback_link}', $this->getFeedbackLinkForAttendee($attendee), $content);

        return $content;
    }

    public function getRsvpLinkForAttendee(Attendee $attendee): string
    {
        if ($attendee->event_id !== $this->event_id) {
            return '';
        }
        return \Illuminate\Support\Facades\URL::signedRoute('rsvp.show', [
            'event' => $this->event,
            'attendee' => $attendee,
        ], now()->addDays(30));
    }

    public function getFeedbackLinkForAttendee(Attendee $attendee): string
    {
        if ($attendee->event_id !== $this->event_id) {
            return '';
        }
        return \Illuminate\Support\Facades\URL::signedRoute('feedback.show', [
            'event' => $this->event,
            'attendee' => $attendee,
        ], now()->addDays(90));
    }
}
