<?php

namespace App\Http\Requests\Auth;

use App\Enums\PartnerType;
use Illuminate\Validation\Rule;

class RegisterPartnerRequest extends RegisterRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'partner_type' => ['required', Rule::enum(PartnerType::class)],
            'business_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'business_phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'license_number' => ['required_if:partner_type,architect', 'nullable', 'string', 'max:100'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
        ]);
    }
}
