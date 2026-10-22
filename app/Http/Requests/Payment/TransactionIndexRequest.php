<?php

namespace App\Http\Requests\Payment;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(TransactionStatus::class)],
            'type' => ['nullable', Rule::enum(TransactionType::class)],
            'needs_review' => ['nullable', 'boolean'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => array_merge(['nullable', 'date_format:Y-m-d'], $this->filled('from') ? ['after_or_equal:from'] : []),
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
