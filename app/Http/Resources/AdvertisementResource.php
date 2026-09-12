<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdvertisementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'headline' => $this->headline,
            'description' => $this->description,
            'category' => $this->category,
            'ad_type' => $this->ad_type,
            'image_url' => $this->image_url,
            'video_url' => $this->video_url,
            'cta_text' => $this->cta_text,
            'brand_color' => $this->brand_color,
            'website' => $this->www,
        ];
    }
}
