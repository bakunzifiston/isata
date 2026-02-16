<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'email',
        'phone',
        'address',
        'subscription_plan_id',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function usage(): HasMany
    {
        return $this->hasMany(OrganizationUsage::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function messageTemplates(): HasMany
    {
        return $this->hasMany(MessageTemplate::class, 'organization_id');
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class, 'organization_id');
    }

    public function socialPosts(): HasMany
    {
        return $this->hasMany(SocialPost::class, 'organization_id');
    }

    public function beepCalls(): HasMany
    {
        return $this->hasMany(BeepCall::class, 'organization_id');
    }

    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class, 'organization_id');
    }

    public function admins(): HasMany
    {
        return $this->users()->where('role', 'admin');
    }

    public function staff(): HasMany
    {
        return $this->users()->where('role', 'staff');
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? Storage::disk('public')->url($this->logo) : null;
    }
}
