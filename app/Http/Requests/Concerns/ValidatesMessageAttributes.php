<?php

namespace App\Http\Requests\Concerns;

use App\Models\Channel;
use App\Models\Message;
use App\Support\StructuredMessageDocument;

trait ValidatesMessageAttributes
{
    protected function isBeepCallChannel(): bool
    {
        $channelId = $this->input('channel_id');
        if (! $channelId) {
            return false;
        }

        $channel = Channel::find($channelId);

        return $channel !== null && $channel->slug === Channel::SLUG_BEEP_CALL;
    }

    protected function prepareMessageAttributesForValidation(): void
    {
        if ($this->isBeepCallChannel()) {
            $this->merge([
                'content_type' => Message::CONTENT_TYPE_TEXT,
                'content' => filled(trim((string) $this->input('content')))
                    ? $this->input('content')
                    : 'Voice reminder',
            ]);

            return;
        }

        if ($this->input('content_type') !== Message::CONTENT_TYPE_STRUCTURED) {
            return;
        }

        $raw = $this->input('content_document');
        if (! is_string($raw) || ! filled(trim($raw))) {
            return;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return;
        }

        $normalized = StructuredMessageDocument::normalize($decoded);

        $this->merge([
            'content_document' => json_encode($normalized),
            'content' => StructuredMessageDocument::buildPlainPreview($normalized, 500),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messageAttributeMessages(): array
    {
        return [
            'content.required' => 'Add a message body in Plain text mode, or switch to Structured layout.',
            'content_document.required' => 'Build your message in the Structured layout editor.',
            'audio_file.required' => 'Upload an audio file for this beep call message.',
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function messageAttributeRules(?Message $existing): array
    {
        $rules = [
            'channel_id' => ['required', 'exists:channels,id'],
            'content_type' => ['required', 'in:text,image,structured'],
            'scheduled_at' => ['nullable', 'date'],
            'status' => $existing
                ? ['required', 'in:draft,scheduled,queued,sent,failed']
                : ['required', 'in:draft,scheduled'],
        ];

        $isBeepCall = $this->isBeepCallChannel();

        switch ($this->input('content_type')) {
            case Message::CONTENT_TYPE_TEXT:
                $rules['content'] = $isBeepCall
                    ? ['nullable', 'string', 'max:10000']
                    : ['required', 'string', 'max:10000'];

                break;
            case Message::CONTENT_TYPE_STRUCTURED:
                $rules['content_document'] = ['required', 'string', 'max:131072'];
                $rules['content'] = ['nullable', 'string', 'max:500'];
                $rules['structure_images'] = ['nullable', 'array'];
                $rules['structure_images.*'] = ['sometimes', 'file', 'image', 'max:5120', 'mimes:jpeg,jpg,png,gif,webp'];

                break;
            default:
                $rules['content'] = ['nullable', 'string', 'max:2000'];
                $rules['content_image'] = ['nullable', 'file', 'image', 'max:5120', 'mimes:jpeg,jpg,png,gif,webp'];
                $rules['remove_content_image'] = ['sometimes', 'boolean'];
        }

        return $rules;
    }
}
