<?php

namespace App\Http\Requests\Concerns;

use App\Models\Channel;

trait ValidatesMessageTemplateAttributes
{
    /**
     * @return array<string, array<int, mixed>>
     */
    protected function messageTemplateAttributeRules(): array
    {
        $channel = Channel::findOrFail($this->input('channel_id'));

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'channel_id' => ['required', 'exists:channels,id'],
            'content' => ['required', 'string', 'max:10000'],
        ];

        if ($channel->supports_subject) {
            $rules['subject'] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }
}
