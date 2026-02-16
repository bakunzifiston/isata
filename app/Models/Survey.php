<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    public const QUESTION_TEXT = 'text';
    public const QUESTION_RATING = 'rating';
    public const QUESTION_SELECT = 'select';
    public const QUESTION_MULTIPLE = 'multiple';

    protected $fillable = [
        'organization_id',
        'event_id',
        'name',
        'description',
        'questions',
        'thank_you_message',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'questions' => 'array',
            'is_active' => 'boolean',
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

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public static function questionTypes(): array
    {
        return [
            self::QUESTION_TEXT => 'Text',
            self::QUESTION_RATING => 'Rating (1-5)',
            self::QUESTION_SELECT => 'Single choice',
            self::QUESTION_MULTIPLE => 'Multiple choice',
        ];
    }
}
