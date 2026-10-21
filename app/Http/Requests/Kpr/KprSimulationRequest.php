<?php

namespace App\Http\Requests\Kpr;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Contoh body:
 * { "property_price": 850000000, "dp_percent": 20, "interest_rate": 7.5, "tenor_years": 20,
 *   "fixed_years": 3, "floating_rate": 11, "include_schedule": false }
 */
class KprSimulationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'property_price' => ['required', 'numeric', 'min:1', 'max:99999999999999'],
            'dp_amount' => ['nullable', 'numeric', 'min:0', 'required_without:dp_percent'],
            'dp_percent' => ['nullable', 'numeric', 'min:0', 'max:99', 'required_without:dp_amount'],
            'interest_rate' => ['required', 'numeric', 'min:0', 'max:30'],
            'tenor_years' => ['required', 'integer', 'min:1', 'max:30'],
            'fixed_years' => ['nullable', 'integer', 'min:1', 'lt:tenor_years'],
            'floating_rate' => ['nullable', 'numeric', 'min:0', 'max:30', 'required_with:fixed_years'],
            'include_schedule' => ['nullable', 'boolean'],
        ];
    }
}
