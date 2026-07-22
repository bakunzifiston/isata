<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailSenderIdentity extends Model
{
    protected $fillable = [
        'organization_id',
        'label',
        'from_name',
        'from_email',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_identity_id');
    }

    public function displayLabel(): string
    {
        if ($this->label) {
            return $this->label.' · '.$this->from_name.' <'.$this->from_email.'>';
        }

        return $this->from_name.' <'.$this->from_email.'>';
    }
}
