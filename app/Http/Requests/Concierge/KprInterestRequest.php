<?php

namespace App\Http\Requests\Concierge;

use Illuminate\Foundation\Http\FormRequest;

class KprInterestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[0-9]{8,12}$/'],
            'loan_amount' => ['required', 'integer', 'min:10000000', 'max:99999999999'],
            'monthly_income' => ['required', 'integer', 'min:1000000', 'max:99999999999'],
            'tenor_years' => ['nullable', 'integer', 'min:1', 'max:30'],
            'property_id' => ['nullable', 'integer', 'min:1'],
            'consent' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_phone.regex' => 'Format nomor HP tidak valid (contoh: 081234567890).',
            'consent.accepted' => 'Persetujuan meneruskan data ke bank rekanan wajib diberikan.',
        ];
    }
}
