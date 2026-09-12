<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'event_type' => $this->event_type,
            'single_event_date' => $this->single_event_date,
            'single_start_time' => $this->single_start_time,
            'single_end_time' => $this->single_end_time,
            'recurr_pattern' => $this->recurr_pattern,
            'recurr_start_date' => $this->recurr_start_date,
            'recurr_end_date' => $this->recurr_end_date,
            'seating_plan' => (bool) $this->seating_plan,
            'enable_views' => (bool) $this->enable_views,
            'image_gallery' => $this->image_gallery,
            'artists' => $this->artists,
            'twitter' => $this->twitter,
            'instagram' => $this->instagram,
            'facebook' => $this->facebook,
            'tiktok' => $this->tiktok,
            'linkedin' => $this->linkedin,
        ];
    }
}
