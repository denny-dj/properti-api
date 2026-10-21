<?php

namespace App\Http\Requests\Architect;

use App\Enums\ServiceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreArchitectServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'service_type' => ['required', Rule::enum(ServiceType::class)],
            'price' => ['required', 'numeric', 'min:1', 'max:99999999999999'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480', 'multiple_of:15'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
