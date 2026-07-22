<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesEventAttributes;
use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
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
        return $this->eventAttributeRules(false);
    }

    /**
     * @return array<string, mixed>
     */
    public function normalized(): array
    {
        return $this->normalizeEventAttributes($this->validated());
    }
}
