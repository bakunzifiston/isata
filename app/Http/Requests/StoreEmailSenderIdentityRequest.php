<?php

namespace App\Http\Requests;

use App\Models\EmailSenderIdentity;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmailSenderIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', EmailSenderIdentity::class);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:100'],
            'from_name' => ['required', 'string', 'max:255'],
            'from_email' => ['required', 'string', 'email', 'max:255'],
        ];
    }
}
