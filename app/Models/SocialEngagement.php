<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialEngagement extends Model
{
    public const TYPE_LIKE = 'like';
    public const TYPE_SHARE = 'share';
    public const TYPE_COMMENT = 'comment';
    public const TYPE_REPLY = 'reply';
    public const TYPE_VIEW = 'view';

    protected $table = 'social_engagement';

    protected $fillable = [
        'social_post_id',
        'platform',
        'engagement_type',
        'count',
        'external_id',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'fetched_at' => 'datetime',
        ];
    }

    public function socialPost(): BelongsTo
    {
        return $this->belongsTo(SocialPost::class);
    }
}
