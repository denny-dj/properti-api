<?php

namespace App\Http\Requests\Property;

use App\Enums\ListingType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Query string untuk listing/filter properti.
 * Contoh: /api/v1/properties?city=Depok&max_price=1000000000&facilities[]=carport&lat=-6.4&lng=106.8&radius_km=5&sort=nearest
 */
class PropertyIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'property_type' => ['nullable', Rule::enum(PropertyType::class)],
            'listing_type' => ['nullable', Rule::enum(ListingType::class)],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'certificate' => ['nullable', 'string', 'max:20'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => array_merge(['nullable', 'numeric', 'min:0'], $this->filled('min_price') ? ['gte:min_price'] : []),
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'min_land_area' => ['nullable', 'numeric', 'min:0'],
            'max_land_area' => ['nullable', 'numeric', 'min:0'],
            'facilities' => ['nullable', 'array', 'max:12'],
            'facilities.*' => ['string', 'max:100'],
            'lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng'],
            'lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat'],
            'radius_km' => ['nullable', 'numeric', 'min:0.1', 'max:100'],
            'sort' => ['nullable', Rule::in(['newest', 'price_asc', 'price_desc', 'nearest'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            // Hanya dipakai di endpoint partner/admin (diabaikan di listing publik):
            'status' => ['nullable', Rule::enum(PropertyStatus::class)],
            'partner_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
