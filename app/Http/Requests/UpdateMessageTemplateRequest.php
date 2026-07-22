<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesMessageTemplateAttributes;
use App\Models\MessageTemplate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMessageTemplateRequest extends FormRequest
{
    use ValidatesMessageTemplateAttributes;

    public function authorize(): bool
    {
        /** @var MessageTemplate $template */
        $template = $this->route('template');

        return $this->user()->can('update', $template);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return $this->messageTemplateAttributeRules();
    }
}
