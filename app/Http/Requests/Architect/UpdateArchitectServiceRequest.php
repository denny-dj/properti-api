<?php

namespace App\Http\Requests\Architect;

class UpdateArchitectServiceRequest extends StoreArchitectServiceRequest
{
    public function rules(): array
    {
        return collect(parent::rules())
            ->map(fn (array $rules) => array_merge(['sometimes'], $rules))
            ->all();
    }
}
