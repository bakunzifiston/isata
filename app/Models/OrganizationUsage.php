<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationUsage extends Model
{
    protected $table = 'organization_usage';

    protected $fillable = [
        'organization_id',
        'period',
        'events_count',
        'contacts_count',
        'beep_calls_count',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public static function getOrCreateForPeriod(int $organizationId, string $period): self
    {
        return self::firstOrCreate(
            ['organization_id' => $organizationId, 'period' => $period],
            ['events_count' => 0, 'contacts_count' => 0, 'beep_calls_count' => 0]
        );
    }
}
