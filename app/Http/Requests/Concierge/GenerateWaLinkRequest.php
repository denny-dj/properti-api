<?php

namespace App\Http\Requests\Concierge;

use App\Enums\LeadSource;
use App\Enums\PaymentPreference;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateWaLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source' => ['required', Rule::enum(LeadSource::class)],
            'reference_id' => ['nullable', 'integer', 'min:1', 'required_if:source,property_detail', 'required_if:source,architect_portfolio'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'regex:/^(\+62|62|0)8[0-9]{8,12}$/'],
            'estimated_budget' => ['nullable', 'integer', 'min:0', 'max:99999999999999'],
            'payment_preference' => ['nullable', Rule::enum(PaymentPreference::class)],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return ['customer_phone.regex' => 'Format nomor HP tidak valid (contoh: 081234567890).'];
    }
}
