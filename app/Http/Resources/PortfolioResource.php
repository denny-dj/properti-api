<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortfolioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $rp = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'category' => $this->category->value,
            'category_label' => $this->category->label(),
            'architectural_style' => $this->architectural_style->value,
            'style_label' => $this->architectural_style->label(),
            'estimated_rab' => $this->estimated_rab,
            'estimated_rab_formatted' => $rp($this->estimated_rab),
            'area_sqm' => (float) $this->area_sqm,
            'cost_per_sqm' => $this->cost_per_sqm,
            'cost_per_sqm_formatted' => $rp($this->cost_per_sqm),
            // deskripsi lengkap hanya di detail (relasi images dimuat)
            'description' => $this->when($this->relationLoaded('images'), $this->description),
            'is_ready_to_build' => $this->is_ready_to_build,
            'blueprint' => $this->when($this->is_ready_to_build, fn () => [
                'price' => $this->blueprint_price,
                'price_formatted' => $rp($this->blueprint_price),
                'deliverables' => $this->blueprint_deliverables ?? [],
            ]),
            'status' => $this->status->value,
            'cover_image' => PortfolioImageResource::make($this->whenLoaded('coverImage')),
            'images' => PortfolioImageResource::collection($this->whenLoaded('images')),
            'architect' => $this->whenLoaded('architect', fn () => [
                'id' => $this->architect->id,
                'name' => $this->architect->name,
                'business_name' => $this->architect->partnerProfile?->business_name,
                'city' => $this->architect->partnerProfile?->city,
                'license_number' => $this->architect->partnerProfile?->license_number,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
