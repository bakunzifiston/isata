<?php

namespace App\Http\Requests;

use App\Models\Attendee;
use App\Models\Contact;
use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAttendeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Event $event */
        $event = $this->route('event');

        return $this->user()->can('create', [Attendee::class, $event]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'organization' => ['nullable', 'string', 'max:255'],
            'rsvp_status' => ['required', 'in:pending,confirmed,declined,attended'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Event $event */
            $event = $this->route('event');
            $email = strtolower(trim((string) $this->input('email')));

            $contact = Contact::query()
                ->where('organization_id', $event->organization_id)
                ->where('email', $email)
                ->first();

            if ($contact && $event->attendees()->where('contact_id', $contact->id)->exists()) {
                $validator->errors()->add('email', 'This contact is already on the event guest list.');
            }
        });
    }

    /**
     * @return array{name: string, email: string, phone?: ?string, company?: ?string, rsvp_status: string}
     */
    public function contactAttributes(): array
    {
        return [
            'name' => $this->input('name'),
            'email' => $this->input('email'),
            'phone' => $this->input('phone'),
            'company' => $this->input('organization'),
            'rsvp_status' => $this->input('rsvp_status'),
        ];
    }
}
