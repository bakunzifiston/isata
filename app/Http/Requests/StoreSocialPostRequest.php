<?php

namespace App\Http\Requests;

use App\Models\Message;
use Illuminate\Foundation\Http\FormRequest;

class StoreSocialPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Message::class);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'platform' => ['required', 'in:facebook,linkedin,twitter,whatsapp'],
            'content' => ['required', 'string', 'max:10000'],
            'event_id' => ['required', 'exists:events,id'],
            'social_account_id' => ['nullable', 'exists:social_accounts,id'],
            'scheduled_at' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,scheduled'],
            'media' => ['nullable', 'array'],
            'media.*' => ['file', 'image', 'max:10240'],
        ];
    }
}
