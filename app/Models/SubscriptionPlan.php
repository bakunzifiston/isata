<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    public const SLUG_FREEMIUM = 'freemium';
    public const SLUG_BASIC = 'basic';
    public const SLUG_PRO = 'pro';
    public const SLUG_PREMIUM = 'premium';

    protected $fillable = [
        'name',
        'slug',
        'price',
        'interval',
        'limits',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'limits' => 'array',
        ];
    }

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'subscription_plan_id');
    }

    public function getEventsLimitAttribute(): ?int
    {
        return $this->limits['events_per_month'] ?? null;
    }

    public function getContactsLimitAttribute(): ?int
    {
        return $this->limits['contacts'] ?? null;
    }

    public function hasBeepCalls(): bool
    {
        return ($this->limits['beep_calls'] ?? false) === true;
    }

    public function isUnlimited(): bool
    {
        return $this->getEventsLimitAttribute() === null && $this->getContactsLimitAttribute() === null;
    }

    public function formatLimit(string $key): string
    {
        $value = $this->limits[$key] ?? null;
        if ($key === 'beep_calls') {
            return $value ? 'Included' : '—';
        }
        if ($value === null) {
            return 'Unlimited';
        }
        if ($key === 'events_per_month') {
            return $value . ' event' . ($value != 1 ? 's' : '') . '/month';
        }
        return (string) $value;
    }
}
