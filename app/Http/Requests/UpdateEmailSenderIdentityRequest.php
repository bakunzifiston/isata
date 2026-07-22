<?php

namespace App\Http\Requests;

use App\Models\EmailSenderIdentity;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailSenderIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var EmailSenderIdentity $identity */
        $identity = $this->route('identity');

        return $this->user()->can('update', $identity);
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
