<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdBannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'placement' => $this->placement->value,
            'image_url' => $this->imageUrl(),
            'target_url' => $this->target_url,
            'click_url' => url('/api/v1/content/promotions/'.$this->id.'/click'),
            'start_date' => $this->start_date->toDateString(),
            'end_date' => $this->end_date->toDateString(),
        ];
    }
}
