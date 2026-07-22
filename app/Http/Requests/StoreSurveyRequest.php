<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesSurveyAttributes;
use App\Models\Survey;
use Illuminate\Foundation\Http\FormRequest;

class StoreSurveyRequest extends FormRequest
{
    use ValidatesSurveyAttributes;

    public function authorize(): bool
    {
        return $this->user()->can('create', Survey::class);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return $this->surveyAttributeRules();
    }
}
