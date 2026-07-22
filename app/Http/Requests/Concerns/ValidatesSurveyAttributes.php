<?php

namespace App\Http\Requests\Concerns;

trait ValidatesSurveyAttributes
{
    /**
     * @return array<string, array<int, mixed>>
     */
    protected function surveyAttributeRules(): array
    {
        return [
            'event_id' => ['required', 'exists:events,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'questions' => ['required', 'array'],
            'questions.*.id' => ['required', 'string'],
            'questions.*.type' => ['required', 'in:text,rating,select,multiple'],
            'questions.*.label' => ['required', 'string', 'max:500'],
            'questions.*.options' => ['nullable'],
            'questions.*.required' => ['nullable', 'boolean'],
            'thank_you_message' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
