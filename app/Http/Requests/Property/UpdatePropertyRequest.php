<?php

namespace App\Http\Requests\Property;

/** Sama dengan Store, tetapi semua field opsional (partial update). */
class UpdatePropertyRequest extends StorePropertyRequest
{
    public function rules(): array
    {
        return collect(parent::rules())
            ->map(fn (array $rules) => array_merge(['sometimes'], $rules))
            ->all();
    }
}
