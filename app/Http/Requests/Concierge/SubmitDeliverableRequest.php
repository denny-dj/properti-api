<?php

namespace App\Http\Requests\Concierge;

use App\Support\DeliverableUrl;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class SubmitDeliverableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'deliverable_link' => [
                'required', 'string', 'max:500', 'url:https',
                function (string $attribute, mixed $value, Closure $fail) {
                    if (! DeliverableUrl::isAllowed((string) $value)) {
                        $fail('Tautan harus ke Google Drive (host cloud yang diizinkan) dan berawalan https.');
                    }
                },
            ],
        ];
    }
}
