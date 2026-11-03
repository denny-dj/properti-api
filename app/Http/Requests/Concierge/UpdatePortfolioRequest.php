<?php

namespace App\Http\Requests\Concierge;

/** Sama dengan Store, semua field opsional; ditambah penghapusan foto. */
class UpdatePortfolioRequest extends StorePortfolioRequest
{
    public function rules(): array
    {
        return collect(parent::rules())
            ->map(fn (array $rules) => array_merge(['sometimes'], $rules))
            ->merge([
                'remove_image_ids' => ['nullable', 'array', 'max:10'],
                'remove_image_ids.*' => ['integer'],
            ])
            ->all();
    }
}
