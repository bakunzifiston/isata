<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class SocialPost extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'organization_id',
        'event_id',
        'social_account_id',
        'platform',
        'content',
        'media_paths',
        'status',
        'scheduled_at',
        'published_at',
        'external_post_id',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'media_paths' => 'array',
            'scheduled_at' => 'datetime',
            'published_at' => 'datetime',
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

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class);
    }

    public function engagement(): HasMany
    {
        return $this->hasMany(SocialEngagement::class, 'social_post_id');
    }

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
}
