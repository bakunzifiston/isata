<?php

namespace App\Models;

use App\Support\SignedUrlTtl;
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

    public const CONTENT_TYPE_TEXT = 'text';

    public const CONTENT_TYPE_IMAGE = 'image';

    public const CONTENT_TYPE_STRUCTURED = 'structured';

    protected $fillable = [
        'organization_id',
        'event_id',
        'channel_id',
        'social_account_id',
        'social_platform',
        'sender_user_id',
        'sender_identity_id',
        'sender_custom_name',
        'sender_custom_email',
        'subject',
        'content',
        'content_type',
        'content_document',
        'content_image',
        'audio_file',
        'attachment_file',
        'media_paths',
        'scheduled_at',
        'status',
        'external_post_id',
        'error_message',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'published_at' => 'datetime',
            'content_document' => 'array',
            'media_paths' => 'array',
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

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class, 'social_account_id');
    }

    public function engagement(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SocialEngagement::class, 'message_id');
    }

    public function isSocialMessage(): bool
    {
        $this->loadMissing('channel');

        return $this->channel?->slug === Channel::SLUG_SOCIAL_MEDIA;
    }

    public function getPlatformAttribute(): ?string
    {
        return $this->social_platform;
    }

    /** @return array<int, string> */
    public function getMediaUrlsAttribute(): array
    {
        if (! $this->media_paths) {
            return [];
        }

        return array_map(fn ($path) => Storage::disk('public')->url($path), $this->media_paths);
    }

    public function getTotalEngagementAttribute(): int
    {
        return (int) $this->engagement()->sum('count');
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function senderIdentity(): BelongsTo
    {
        return $this->belongsTo(EmailSenderIdentity::class, 'sender_identity_id');
    }

    /** @return array{name: string, email: string} */
    public function resolveEmailSender(): array
    {
        $this->loadMissing(['senderIdentity', 'sentBy']);

        if (filled($this->sender_custom_email)) {
            return [
                'name' => filled($this->sender_custom_name) ? (string) $this->sender_custom_name : explode('@', (string) $this->sender_custom_email)[0],
                'email' => (string) $this->sender_custom_email,
            ];
        }

        if ($this->senderIdentity) {
            return [
                'name' => (string) $this->senderIdentity->from_name,
                'email' => (string) $this->senderIdentity->from_email,
            ];
        }

        if ($this->sentBy) {
            return [
                'name' => (string) $this->sentBy->name,
                'email' => (string) $this->sentBy->email,
            ];
        }

        return [
            'name' => (string) config('app.name', 'ISATA'),
            'email' => (string) (config('mail.from.address') ?: 'noreply@example.invalid'),
        ];
    }

    public function getResolvedEmailSenderLineAttribute(): string
    {
        $this->loadMissing('channel');
        if (! $this->channel || $this->channel->slug !== Channel::SLUG_EMAIL) {
            return '';
        }
        $r = $this->resolveEmailSender();

        return $r['name'].' · '.$r['email'];
    }

    public function getAudioUrlAttribute(): ?string
    {
        return $this->audio_file ? Storage::disk('public')->url($this->audio_file) : null;
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (! $this->attachment_file) {
            return null;
        }

        // Attachments are on the private disk; use the authenticated download route.
        return route('messages.attachment.download', $this);
    }

    public function getContentImageUrlAttribute(): ?string
    {
        return $this->content_image ? Storage::disk('public')->url($this->content_image) : null;
    }

    public function isImageContent(): bool
    {
        return ($this->content_type ?? self::CONTENT_TYPE_TEXT) === self::CONTENT_TYPE_IMAGE
            && filled($this->content_image);
    }

    public function isStructuredContent(): bool
    {
        return ($this->content_type ?? self::CONTENT_TYPE_TEXT) === self::CONTENT_TYPE_STRUCTURED
            && is_array($this->content_document ?? null);
    }

    public function structuredPreview(?int $limit = null): ?string
    {
        if (! $this->isStructuredContent()) {
            return null;
        }

        $doc = \App\Support\StructuredMessageDocument::normalize($this->content_document ?? []);

        return \App\Support\StructuredMessageDocument::buildPlainPreview($doc, $limit ?? 220);
    }

    public function renderContentForAttendee(Attendee $attendee): string
    {
        $this->loadMissing('channel');

        if ($this->isStructuredContent()) {
            $doc = \App\Support\StructuredMessageDocument::normalize($this->content_document ?? []);
            $isEmailHtml = $this->channel && $this->channel->slug === Channel::SLUG_EMAIL;

            return \App\Support\StructuredMessageDocument::renderForAttendee($doc, $this, $attendee, $isEmailHtml);
        }

        if ($this->isImageContent()) {
            $caption = $this->personalizeContentTokens((string) ($this->content ?? ''), $attendee);
            $url = $this->content_image_url;

            if ($this->channel && $this->channel->slug === Channel::SLUG_EMAIL) {
                $html = '';
                if (filled($caption)) {
                    $html .= '<p>'.e($caption).'</p>';
                }
                $html .= '<p><img src="'.e($url).'" alt="" style="max-width:100%;height:auto;border-radius:0.5rem;"></p>';

                return $html;
            }

            return trim($caption.(filled($caption) ? "\n\n" : '').$url);
        }

        return $this->personalizeContentTokens((string) ($this->content ?? ''), $attendee);
    }

    public function deleteStructuredImageFiles(?array $document = null): void
    {
        $paths = \App\Support\StructuredMessageDocument::collectImagePaths(
            \App\Support\StructuredMessageDocument::normalize($document ?? $this->content_document ?? [])
        );
        foreach ($paths as $path) {
            Storage::disk('public')->delete($path);
        }
    }

    public function personalizeContentTokens(string $content, Attendee $attendee): string
    {
        $event = $this->event;

        $content = str_replace('{name}', $attendee->name, $content);
        $content = str_replace('{event_name}', $event->name ?? '', $content);
        $content = str_replace('{event_time}', $event->date?->format('M j, Y').($event->time_formatted ? ' at '.$event->time_formatted : ''), $content);
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
        ], SignedUrlTtl::rsvpExpiresAt());
    }

    public function getFeedbackLinkForAttendee(Attendee $attendee): string
    {
        if ($attendee->event_id !== $this->event_id) {
            return '';
        }

        return \Illuminate\Support\Facades\URL::signedRoute('feedback.show', [
            'event' => $this->event,
            'attendee' => $attendee,
        ], SignedUrlTtl::feedbackExpiresAt());
    }
}
