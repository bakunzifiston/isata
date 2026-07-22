<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesMessageAttributes;
use App\Http\Requests\Concerns\ValidatesMessageChannelAttributes;
use App\Models\Message;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMessageRequest extends FormRequest
{
    use ValidatesMessageAttributes;
    use ValidatesMessageChannelAttributes;

    public function authorize(): bool
    {
        /** @var Message $message */
        $message = $this->route('message');

        return $this->user()->can('update', $message);
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
        /** @var Message $message */
        $message = $this->route('message');

        return $this->mergeMessageChannelRules($this->messageAttributeRules($message), true, $message);
    }
}
