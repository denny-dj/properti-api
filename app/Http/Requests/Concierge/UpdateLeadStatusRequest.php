<?php

namespace App\Http\Requests\Concierge;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['contacted', 'surveying', 'committed', 'lost'])],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
