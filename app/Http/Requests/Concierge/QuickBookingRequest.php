<?php

namespace App\Http\Requests\Concierge;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuickBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $min = (float) config('concierge.fees.architect_min_percent', 10);
        $max = (float) config('concierge.fees.architect_max_percent', 15);

        return [
            'type' => ['required', Rule::in(['property_booking_fee', 'architect_blueprint', 'architect_custom'])],
            'buyer_id' => ['nullable', 'integer', 'exists:users,id', 'required_without:buyer_email'],
            'buyer_email' => ['nullable', 'email', 'required_without:buyer_id'],
            'property_id' => ['nullable', 'integer', 'exists:properties,id', 'required_if:type,property_booking_fee'],
            'portfolio_id' => ['nullable', 'integer', 'exists:architect_portfolios,id', 'required_if:type,architect_blueprint', 'required_if:type,architect_custom'],
            // Properti: opsional (default aturan booking fee). Blueprint: diabaikan (harga paket). Custom: wajib.
            'amount' => ['nullable', 'integer', 'min:10000', 'max:99999999999', Rule::requiredIf(fn () => $this->input('type') === 'architect_custom')],
            'commission_percent' => ['nullable', 'numeric', "min:{$min}", "max:{$max}"],
            'lead_code' => ['nullable', 'string', 'max:24', 'exists:concierge_leads,lead_code'],
            'expires_in_hours' => ['nullable', 'integer', 'min:1', 'max:72'],
        ];
    }
}
