<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'partner_type' => $this->partner_type->value,
            'business_name' => $this->business_name,
            'description' => $this->description,
            'business_phone' => $this->business_phone,
            'address' => $this->address,
            'city' => $this->city,
            'license_number' => $this->license_number,
            'portfolio_url' => $this->portfolio_url,
            'verification_status' => $this->verification_status->value,
            'verified_at' => $this->verified_at?->toIso8601String(),
            'rejection_reason' => $this->rejection_reason,
            'user' => UserResource::make($this->whenLoaded('user')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
