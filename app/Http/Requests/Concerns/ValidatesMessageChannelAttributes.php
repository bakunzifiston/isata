<?php

namespace App\Http\Requests\Concerns;

use App\Models\Channel;
use App\Models\Message;
use Illuminate\Validation\Rule;

trait ValidatesMessageChannelAttributes
{
    /**
     * @param  array<string, array<int, mixed>>  $rules
     * @return array<string, array<int, mixed>>
     */
    protected function mergeMessageChannelRules(array $rules, bool $isUpdate, ?Message $existing = null): array
    {
        $channel = Channel::findOrFail($this->input('channel_id'));
        $organizationId = $this->user()->organization?->id;

        if ($channel->slug === Channel::SLUG_EMAIL) {
            $rules['email_sender_type'] = ['required', 'in:saved,custom'];

            if ($this->input('email_sender_type') === 'custom') {
                $rules['sender_identity_id'] = ['nullable'];
                $rules['sender_custom_name'] = ['required', 'string', 'max:255'];
                $rules['sender_custom_email'] = ['required', 'string', 'email', 'max:255'];
            } else {
                $rules['sender_identity_id'] = [
                    'required',
                    Rule::exists('email_sender_identities', 'id')->where(
                        fn ($q) => $q->where('organization_id', $organizationId)
                    ),
                ];
                $rules['sender_custom_name'] = ['nullable'];
                $rules['sender_custom_email'] = ['nullable'];
            }
        }

        if ($channel->supports_subject) {
            $rules['subject'] = ['nullable', 'string', 'max:255'];
        }

        if ($channel->supports_audio) {
            $hasExistingAudio = $isUpdate && $existing && filled($existing->audio_file);
            $rules['audio_file'] = [
                Rule::requiredIf(! $hasExistingAudio),
                'nullable',
                'file',
                'mimes:mp3,wav,m4a',
                'max:10240',
            ];
        }

        if ($channel->supports_attachment) {
            $rules['attachment_file'] = [
                'nullable',
                'file',
                'max:10240',
                'mimes:pdf,jpeg,jpg,png,gif,webp,txt,csv,doc,docx',
            ];

            if ($isUpdate) {
                $rules['remove_attachment'] = ['sometimes', 'boolean'];
            }
        }

        return $rules;
    }
}
