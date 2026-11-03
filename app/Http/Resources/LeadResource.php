<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lead_code' => $this->lead_code,
            'source' => $this->source->value,
            'reference_id' => $this->reference_id,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'wa_chat_url' => $this->customer_phone ? 'https://wa.me/'.$this->customer_phone : null,
            'estimated_budget' => $this->estimated_budget,
            'payment_preference' => $this->payment_preference?->value,
            'status' => $this->status->value,
            'notes' => $this->notes,
            'handled_by' => $this->whenLoaded('handler', fn () => $this->handler ? ['id' => $this->handler->id, 'name' => $this->handler->name] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
