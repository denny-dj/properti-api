<?php

namespace App\Http\Requests\Kpr;

use Illuminate\Foundation\Http\FormRequest;

/** Simulasi untuk satu properti: harga diambil dari listing, semua parameter opsional (ada asumsi default). */
class PropertyKprRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dp_amount' => ['nullable', 'numeric', 'min:0'],
            'dp_percent' => ['nullable', 'numeric', 'min:0', 'max:99'],
            'interest_rate' => ['nullable', 'numeric', 'min:0', 'max:30'],
            'tenor_years' => ['nullable', 'integer', 'min:1', 'max:30'],
            'fixed_years' => ['nullable', 'integer', 'min:1', 'lt:tenor_years'],
            'floating_rate' => ['nullable', 'numeric', 'min:0', 'max:30', 'required_with:fixed_years'],
            'include_schedule' => ['nullable', 'boolean'],
        ];
    }
}
