<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Channel extends Model
{
    public const SLUG_EMAIL = 'email';
    public const SLUG_SMS = 'sms';
    public const SLUG_BEEP_CALL = 'beep_call';
    public const SLUG_SOCIAL_MEDIA = 'social_media';

    protected $fillable = [
        'name',
        'slug',
        'supports_subject',
        'supports_audio',
    ];

    protected function casts(): array
    {
        return [
            'supports_subject' => 'boolean',
            'supports_audio' => 'boolean',
        ];
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function templates(): HasMany
    {
        return $this->hasMany(MessageTemplate::class, 'channel_id');
    }

    public function supportsSubject(): bool
    {
        return $this->supports_subject;
    }

    public function supportsAudio(): bool
    {
        return $this->supports_audio;
    }
}
