<?php

namespace App\Http\Requests\Architect;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'availabilities' => ['present', 'array', 'max:35'],
            'availabilities.*.day_of_week' => ['required', 'integer', 'between:1,7'],
            'availabilities.*.start_time' => ['required', 'date_format:H:i'],
            'availabilities.*.end_time' => ['required', 'date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $byDay = collect($this->input('availabilities', []))->groupBy('day_of_week');

            foreach ($byDay as $day => $rows) {
                $sorted = $rows->sortBy('start_time')->values();

                foreach ($sorted as $i => $row) {
                    if ($row['end_time'] <= $row['start_time']) {
                        $v->errors()->add('availabilities', "Jam selesai harus lebih besar dari jam mulai (hari ke-{$day}).");

                        return;
                    }

                    if ($i > 0 && $row['start_time'] < $sorted[$i - 1]['end_time']) {
                        $v->errors()->add('availabilities', "Jadwal pada hari ke-{$day} saling tumpang tindih.");

                        return;
                    }
                }
            }
        });
    }
}
