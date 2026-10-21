<?php

namespace App\Http\Requests\Booking;

use App\Enums\MeetingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'architect_service_id' => ['required', 'integer', 'exists:architect_services,id'],
            'scheduled_start' => ['required', 'date_format:Y-m-d H:i', 'after:now'],
            'meeting_type' => ['required', Rule::enum(MeetingType::class)],
            'location' => ['required_if:meeting_type,onsite', 'nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
