<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArchitectServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'service_type' => $this->service_type->value,
            'price' => (float) $this->price,
            'price_formatted' => 'Rp '.number_format((float) $this->price, 0, ',', '.'),
            'duration_minutes' => $this->duration_minutes,
            'is_active' => $this->is_active,
            'architect' => $this->whenLoaded('architect', fn () => [
                'id' => $this->architect->id,
                'name' => $this->architect->name,
                'business_name' => $this->architect->partnerProfile?->business_name,
                'city' => $this->architect->partnerProfile?->city,
                'license_number' => $this->architect->partnerProfile?->license_number,
                'portfolio_url' => $this->architect->partnerProfile?->portfolio_url,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
