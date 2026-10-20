<?php

namespace App\Http\Resources;

use App\Enums\PropertyType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $canSeeModeration = $user && ($user->id === $this->partner_id || $user->isAdmin());
        $hasCoordinates = $this->latitude !== null && $this->longitude !== null;

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            // deskripsi lengkap hanya di endpoint detail (relasi images dimuat)
            'description' => $this->when($this->relationLoaded('images'), $this->description),
            'property_type' => $this->property_type->value,
            'property_type_label' => $this->property_type === PropertyType::House ? 'Rumah' : 'Ruko',
            'listing_type' => $this->listing_type->value,
            'status' => $this->status->value,
            'price' => (float) $this->price,
            'price_formatted' => 'Rp '.number_format((float) $this->price, 0, ',', '.'),
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'floors' => $this->floors,
            'land_area' => $this->land_area !== null ? (float) $this->land_area : null,
            'building_area' => $this->building_area !== null ? (float) $this->building_area : null,
            'certificate' => $this->certificate,
            'location' => [
                'address' => $this->address,
                'district' => $this->district,
                'city' => $this->city,
                'province' => $this->province,
                'postal_code' => $this->postal_code,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'maps_url' => $hasCoordinates
                    ? "https://www.google.com/maps/search/?api=1&query={$this->latitude},{$this->longitude}"
                    : null,
                'distance_km' => $this->when(isset($this->distance_km), fn () => round((float) $this->distance_km, 2)),
            ],
            'cover_image' => PropertyImageResource::make($this->whenLoaded('coverImage')),
            'images' => PropertyImageResource::collection($this->whenLoaded('images')),
            'facilities' => FacilityResource::collection($this->whenLoaded('facilities')),
            'partner' => $this->whenLoaded('partner', fn () => [
                'id' => $this->partner->id,
                'name' => $this->partner->name,
                'business_name' => $this->partner->partnerProfile?->business_name,
                'partner_type' => $this->partner->partnerProfile?->partner_type?->value,
            ]),
            'rejection_reason' => $this->when($canSeeModeration, $this->rejection_reason),
            'views_count' => $this->views_count,
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
