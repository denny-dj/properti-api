<?php

namespace App\Http\Requests\Concierge;

use App\Enums\ArchitecturalStyle;
use App\Enums\PortfolioCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PortfolioIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'min_rab' => ['nullable', 'integer', 'min:0'],
            'max_rab' => array_merge(['nullable', 'integer', 'min:0'], $this->filled('min_rab') ? ['gte:min_rab'] : []),
            'category' => ['nullable', Rule::enum(PortfolioCategory::class)],
            'style' => ['nullable', Rule::enum(ArchitecturalStyle::class)],
            'is_ready_to_build' => ['nullable', 'boolean'],
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(['newest', 'rab_asc', 'rab_desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
