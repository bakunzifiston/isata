<?php

namespace App\Http\Requests\Concerns;

use App\Models\Event;
use Illuminate\Validation\Rule;

trait ValidatesEventAttributes
{
    /**
     * @return array<string, array<int, mixed>>
     */
    protected function eventAttributeRules(bool $isUpdate): array
    {
        $statusRule = $isUpdate
            ? ['required', 'in:draft,scheduled,cancelled,completed']
            : ['required', 'in:draft,scheduled'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'date' => ['required', 'date'],
            'time' => ['nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'event_format' => ['required', 'in:physical,online'],
            'venue' => [
                Rule::requiredIf(fn () => $this->input('event_format') === Event::FORMAT_PHYSICAL
                    && $this->input('status') === Event::STATUS_SCHEDULED),
                'nullable',
                'string',
                'max:255',
            ],
            'meeting_link' => [
                Rule::requiredIf(fn () => $this->input('event_format') === Event::FORMAT_ONLINE
                    && $this->input('status') === Event::STATUS_SCHEDULED),
                'nullable',
                'url',
                'max:500',
            ],
            'status' => $statusRule,
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function normalizeEventAttributes(array $validated): array
    {
        $validated['time'] = ! empty($validated['time']) ? $validated['time'].':00' : null;

        if (($validated['event_format'] ?? '') === Event::FORMAT_PHYSICAL) {
            $validated['meeting_link'] = null;

            return $validated;
        }

        if (($validated['event_format'] ?? '') === Event::FORMAT_ONLINE) {
            $validated['venue'] = null;
        }

        return $validated;
    }
}
