<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialAccount extends Model
{
    public const PLATFORM_FACEBOOK = 'facebook';
    public const PLATFORM_LINKEDIN = 'linkedin';
    public const PLATFORM_TWITTER = 'twitter';
    public const PLATFORM_WHATSAPP = 'whatsapp';

    protected $fillable = [
        'organization_id',
        'platform',
        'name',
        'credentials',
        'is_active',
        'external_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(SocialPost::class);
    }

    public static function platforms(): array
    {
        return [
            self::PLATFORM_FACEBOOK => 'Facebook',
            self::PLATFORM_LINKEDIN => 'LinkedIn',
            self::PLATFORM_TWITTER => 'Twitter / X',
            self::PLATFORM_WHATSAPP => 'WhatsApp Business',
        ];
    }
}
