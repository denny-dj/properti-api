<?php

namespace App\Http\Requests\Architect;

use App\Enums\ServiceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArchitectServiceIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'service_type' => ['nullable', Rule::enum(ServiceType::class)],
            'city' => ['nullable', 'string', 'max:100'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => array_merge(['nullable', 'numeric', 'min:0'], $this->filled('min_price') ? ['gte:min_price'] : []),
            'sort' => ['nullable', Rule::in(['newest', 'price_asc', 'price_desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
