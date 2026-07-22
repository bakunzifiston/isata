<?php

namespace App\Http\Requests;

use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UploadBeepCallAudioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('uploadAudio', \App\Models\BeepCall::class);
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
            'audio' => ['required', 'file', 'mimes:mp3,wav,m4a,ogg,webm', 'max:10240'],
        ];
    }
}
