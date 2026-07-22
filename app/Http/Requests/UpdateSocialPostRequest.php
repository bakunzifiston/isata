<?php

namespace App\Http\Requests;

use App\Models\Message;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSocialPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        $organization = $this->user()->organization;
        $channelId = \App\Models\Channel::where('slug', \App\Models\Channel::SLUG_SOCIAL_MEDIA)->value('id');
        $message = Message::query()
            ->where('id', $this->route('post'))
            ->where('organization_id', $organization?->id)
            ->where('channel_id', $channelId)
            ->first();

        return $message && $this->user()->can('update', $message);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'platform' => ['required', 'in:facebook,linkedin,twitter,whatsapp'],
            'content' => ['required', 'string', 'max:10000'],
            'event_id' => ['nullable', 'exists:events,id'],
            'social_account_id' => ['nullable', 'exists:social_accounts,id'],
            'scheduled_at' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,scheduled,published,failed'],
            'media' => ['nullable', 'array'],
            'media.*' => ['file', 'image', 'max:10240'],
        ];
    }
}
