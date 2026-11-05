<?php

namespace App\Http\Requests\Web;

use App\Enums\LeadSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConciergeRedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source' => ['required', Rule::enum(LeadSource::class)],
            'reference_id' => ['nullable', 'integer', 'min:1', 'required_if:source,property_detail', 'required_if:source,architect_portfolio', 'required_if:source,promotion'],
        ];
    }
}
