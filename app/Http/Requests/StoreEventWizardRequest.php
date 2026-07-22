<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesEventAttributes;
use App\Models\Channel;
use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

class StoreEventWizardRequest extends FormRequest
{
    use ValidatesEventAttributes;

    public function authorize(): bool
    {
        return $this->user()->can('create', Event::class);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = $this->eventAttributeRules(false);

        $rules['guests_bulk'] = ['nullable', 'string', 'max:10000'];
        $rules['skip_message'] = ['nullable', 'boolean'];
        $rules['message_channel_id'] = ['nullable', 'exists:channels,id'];
        $rules['message_content'] = ['nullable', 'string', 'max:10000'];
        $rules['message_status'] = ['nullable', 'in:draft,scheduled'];

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public function normalizedEvent(): array
    {
        return $this->normalizeEventAttributes($this->validated());
    }

    public function shouldCreateMessage(): bool
    {
        return ! $this->boolean('skip_message')
            && filled($this->input('message_channel_id'))
            && filled($this->input('message_content'));
    }
}
