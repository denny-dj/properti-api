<?php

namespace App\Http\Requests\Concierge;

use App\Enums\ArchitecturalStyle;
use App\Enums\PortfolioCategory;
use App\Enums\PortfolioStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Aturan lintas-field (paket siap bangun wajib berharga & berdaftar berkas; tayang wajib berfoto)
 * diperiksa di ArchitectPortfolioService pada kondisi akhir, agar benar untuk create maupun update parsial.
 */
class StorePortfolioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'category' => ['required', Rule::enum(PortfolioCategory::class)],
            'architectural_style' => ['required', Rule::enum(ArchitecturalStyle::class)],
            'estimated_rab' => ['required', 'integer', 'min:1000000', 'max:100000000000'],
            'area_sqm' => ['required', 'numeric', 'min:1', 'max:100000'],
            'description' => ['nullable', 'string', 'max:10000'],
            'is_ready_to_build' => ['nullable', 'boolean'],
            'blueprint_price' => ['nullable', 'integer', 'min:100000', 'max:100000000000'],
            'blueprint_deliverables' => ['nullable', 'array', 'max:10'],
            'blueprint_deliverables.*' => ['string', 'max:100'],
            'status' => ['nullable', Rule::enum(PortfolioStatus::class)],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'cover_index' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
