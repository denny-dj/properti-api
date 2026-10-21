<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status->value,
            'scheduled_start' => $this->scheduled_start->format('Y-m-d H:i'),
            'scheduled_end' => $this->scheduled_end->format('Y-m-d H:i'),
            'duration_minutes' => $this->duration_minutes,
            'meeting_type' => $this->meeting_type->value,
            'location' => $this->location,
            'notes' => $this->notes,
            'price' => (float) $this->price,
            'price_formatted' => 'Rp '.number_format((float) $this->price, 0, ',', '.'),
            'service' => ArchitectServiceResource::make($this->whenLoaded('service')),
            'client' => $this->whenLoaded('client', fn () => [
                'id' => $this->client->id,
                'name' => $this->client->name,
                'phone' => $this->client->phone,
            ]),
            'architect' => $this->whenLoaded('architect', fn () => [
                'id' => $this->architect->id,
                'name' => $this->architect->name,
                'business_name' => $this->architect->partnerProfile?->business_name,
            ]),
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'cancel_reason' => $this->cancel_reason,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
