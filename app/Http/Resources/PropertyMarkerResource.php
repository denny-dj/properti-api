<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Representasi ringan untuk pin di peta. */
class PropertyMarkerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'property_type' => $this->property_type->value,
            'city' => $this->city,
            'price' => (float) $this->price,
            'price_formatted' => 'Rp '.number_format((float) $this->price, 0, ',', '.'),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'cover_image' => PropertyImageResource::make($this->whenLoaded('coverImage')),
        ];
    }
}
