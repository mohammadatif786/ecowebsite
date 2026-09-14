<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VibeMediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $url = null;
        if ($this->disk === 'public') {
            $url = asset('storage/' . $this->path);
        } else {
            $url = Storage::disk($this->disk)->url($this->path);
        }

        return [
            'id' => $this->id,
            'type' => $this->media_type->value,
            'source' => $this->source->value,
            'url' => $url,
            'thumbnail_url' => $this->thumbnail_path ? ($this->disk === 'public' ? asset('storage/' . $this->thumbnail_path) : Storage::disk($this->disk)->url($this->thumbnail_path)) : null,
            'mime_type' => $this->mime_type,
            'width' => $this->width,
            'height' => $this->height,
            'duration' => $this->duration,
            'processing_status' => $this->processing_status->value,
            'sort_order' => $this->sort_order,
        ];
    }
}
