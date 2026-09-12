<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LiveStreamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'status' => $this->status,
            'visibility' => $this->visibility,
            'category' => $this->broadcast_type,
            'location' => $this->location,
            'cover' => $this->image_url,
            'started_at' => $this->start_time?->toISOString(),
            'ended_at' => $this->ended_at?->toISOString(),
        ];
    }
}
