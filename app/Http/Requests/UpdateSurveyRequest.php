<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesSurveyAttributes;
use App\Models\Survey;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSurveyRequest extends FormRequest
{
    use ValidatesSurveyAttributes;

    public function authorize(): bool
    {
        /** @var Survey $survey */
        $survey = $this->route('survey');

        return $this->user()->can('update', $survey);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return $this->surveyAttributeRules();
    }
}
