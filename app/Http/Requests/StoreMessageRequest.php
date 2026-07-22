<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesMessageAttributes;
use App\Http\Requests\Concerns\ValidatesMessageChannelAttributes;
use App\Models\Event;
use App\Models\Message;
use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    use ValidatesMessageAttributes;
    use ValidatesMessageChannelAttributes;

    public function authorize(): bool
    {
        /** @var Event $event */
        $event = $this->route('event');

        return $this->user()->can('createForEvent', [Message::class, $event]);
    }

    protected function prepareForValidation(): void
    {
        $this->prepareMessageAttributesForValidation();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->messageAttributeMessages();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return $this->mergeMessageChannelRules($this->messageAttributeRules(null), false, null);
    }
}
