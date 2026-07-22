<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesMessageTemplateAttributes;
use App\Models\MessageTemplate;
use Illuminate\Foundation\Http\FormRequest;

class StoreMessageTemplateRequest extends FormRequest
{
    use ValidatesMessageTemplateAttributes;

    public function authorize(): bool
    {
        return $this->user()->can('create', MessageTemplate::class);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return $this->messageTemplateAttributeRules();
    }
}
