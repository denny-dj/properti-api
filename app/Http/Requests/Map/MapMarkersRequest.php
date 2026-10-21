<?php

namespace App\Http\Requests\Map;

use App\Http\Requests\Property\PropertyIndexRequest;

/**
 * Semua filter listing + bounding box peta.
 * Contoh: /api/v1/properties/markers?south=-6.5&west=106.7&north=-6.2&east=107.0&max_price=1500000000
 */
class MapMarkersRequest extends PropertyIndexRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'south' => ['nullable', 'numeric', 'between:-90,90', 'required_with:west,north,east'],
            'north' => ['nullable', 'numeric', 'between:-90,90', 'required_with:south,west,east'],
            'west' => ['nullable', 'numeric', 'between:-180,180', 'required_with:south,north,east'],
            'east' => ['nullable', 'numeric', 'between:-180,180', 'required_with:south,north,west'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);
    }
}
