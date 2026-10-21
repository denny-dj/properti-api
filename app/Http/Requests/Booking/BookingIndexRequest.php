<?php

namespace App\Http\Requests\Booking;

use App\Enums\BookingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(BookingStatus::class)],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => array_merge(['nullable', 'date_format:Y-m-d'], $this->filled('from') ? ['after_or_equal:from'] : []),
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
