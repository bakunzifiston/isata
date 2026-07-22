<?php

namespace App\Http\Requests;

use App\Models\BeepCall;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBeepCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', BeepCall::class);
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            redirect()
                ->route('subscription.upgrade', ['plan' => SubscriptionPlan::SLUG_PREMIUM])
                ->with('error', 'Beep calls are included on the Premium plan. Upgrade to schedule voice reminders to attendees.')
        );
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'event_id' => ['required', 'exists:events,id'],
            'attendee_ids' => ['required', 'array', 'min:1'],
            'attendee_ids.*' => ['exists:attendees,id'],
            'audio_file' => ['nullable', 'file', 'mimes:mp3,wav,m4a,ogg,webm', 'max:10240'],
            'audio_path' => ['nullable', 'string', 'max:500'],
            'call_schedule' => ['required', 'date', 'after_or_equal:now'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'attendee_ids.required' => 'Select at least one attendee to call.',
            'attendee_ids.min' => 'Select at least one attendee to call.',
            'call_schedule.after_or_equal' => 'Schedule the call for a future time.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->hasFile('audio_file') && ! filled($this->input('audio_path'))) {
                $validator->errors()->add('audio_file', 'Please upload or record audio.');
            }
        });
    }
}
