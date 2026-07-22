<?php

namespace App\Http\Requests;

use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

class BulkImportAttendeeRequest extends FormRequest
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
            'bulk_data' => ['required', 'string', 'max:10000'],
        ];
    }
}
