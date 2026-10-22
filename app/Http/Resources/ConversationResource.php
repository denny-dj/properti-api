<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isBuyer = $request->user()?->id === $this->buyer_id;

        $counterpart = $isBuyer
            ? [
                'id' => $this->partner->id,
                'name' => $this->partner->name,
                'business_name' => $this->partner->partnerProfile?->business_name,
                'role' => 'partner',
            ]
            : [
                'id' => $this->buyer->id,
                'name' => $this->buyer->name,
                'role' => 'buyer',
            ];

        $subject = null;

        if ($this->property) {
            $subject = ['type' => 'property', 'id' => $this->property->id, 'slug' => $this->property->slug, 'title' => $this->property->title];
        } elseif ($this->architectService) {
            $subject = ['type' => 'architect_service', 'id' => $this->architectService->id, 'slug' => $this->architectService->slug, 'title' => $this->architectService->title];
        }

        return [
            'id' => $this->id,
            'counterpart' => $counterpart,
            'subject' => $subject,
            'last_message' => MessageResource::make($this->whenLoaded('latestMessage')),
            'unread_count' => (int) ($this->resource->getAttribute('unread_count') ?? 0),
            'last_message_at' => $this->last_message_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
