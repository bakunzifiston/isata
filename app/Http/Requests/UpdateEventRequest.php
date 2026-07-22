<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesEventAttributes;
use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    use ValidatesEventAttributes;

    public function authorize(): bool
    {
        /** @var Event $event */
        $event = $this->route('event');

        return $this->user()->can('update', $event);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return $this->eventAttributeRules(true);
    }

    /**
     * @return array<string, mixed>
     */
    public function normalized(): array
    {
        return $this->normalizeEventAttributes($this->validated());
    }
}
