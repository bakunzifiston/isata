<?php

namespace App\Http\Requests;

use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionUpgradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $organization = $this->user()->organization;

        return $organization && $this->user()->can('manageBilling', $organization);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'integer', 'exists:subscription_plans,id'],
        ];
    }
}
