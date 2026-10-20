<?php

namespace App\Http\Requests\Property;

use App\Enums\ListingType;
use App\Enums\PropertyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:10000'],
            'property_type' => ['required', Rule::enum(PropertyType::class)],
            'listing_type' => ['required', Rule::enum(ListingType::class)],
            'price' => ['required', 'numeric', 'min:1', 'max:99999999999999'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'floors' => ['nullable', 'integer', 'min:1', 'max:50'],
            'land_area' => ['nullable', 'numeric', 'min:1', 'max:99999999'],
            'building_area' => ['nullable', 'numeric', 'min:1', 'max:99999999'],
            'certificate' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            'district' => ['nullable', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'facility_ids' => ['nullable', 'array', 'max:30'],
            'facility_ids.*' => ['integer', 'distinct', 'exists:facilities,id'],
        ];
    }
}
