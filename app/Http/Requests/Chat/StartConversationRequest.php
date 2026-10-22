<?php

namespace App\Http\Requests\Chat;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/** Isi TEPAT SATU dari: property_id, architect_service_id, partner_id. */
class StartConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
            'architect_service_id' => ['nullable', 'integer', 'exists:architect_services,id'],
            'partner_id' => ['nullable', 'integer', 'exists:users,id'],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $filled = collect(['property_id', 'architect_service_id', 'partner_id'])
                ->filter(fn (string $key) => $this->filled($key))
                ->count();

            if ($filled !== 1) {
                $v->errors()->add('property_id', 'Isi tepat salah satu: property_id, architect_service_id, atau partner_id.');
            }
        });
    }
}
