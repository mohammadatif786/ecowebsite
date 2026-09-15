<?php

namespace App\Http\Resources;

use App\Models\ClubFete;
use App\Models\OrganizerProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VibeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'creator' => $this->whenLoaded('creator', fn () => $this->identity($this->creator)),
            'publisher' => $this->whenLoaded('publisher', fn () => $this->identity($this->publisher)),
            'publisher_type' => $this->publisher_type,
            'caption' => $this->caption,
            'location' => ['name' => $this->location_name, 'place_id' => $this->location_place_id, 'latitude' => $this->latitude, 'longitude' => $this->longitude],
            'allow_coin_gifts' => $this->allow_coin_gifts,
            'visibility' => $this->visibility->value,
            'status' => $this->status->value,
            'published_at' => $this->published_at?->toISOString(),
            'media' => VibeMediaResource::collection($this->whenLoaded('media')),
            'products' => $this->whenLoaded('products', fn () => $this->products->map->only(['id', 'name', 'price', 'cover_image'])),
            'events' => $this->whenLoaded('events', fn () => $this->events->map->only(['id', 'title', 'featured_image', 'venue'])),
            'likes_count' => $this->likes_count,
            'comments_count' => $this->comments_count,
            'shares_count' => $this->shares_count,
            'is_liked' => $this->when(auth()->check(), fn () => $this->likes()->where('user_id', auth()->id())->exists()),
        ];
    }

    private function identity(object $model): array
    {
        return match (true) {
            $model instanceof User => ['id' => $model->id, 'type' => 'user', 'name' => $model->name, 'avatar' => $model->avatar],
            $model instanceof OrganizerProfile => ['id' => $model->id, 'type' => 'organization', 'name' => $model->organizer_name],
            $model instanceof ClubFete => ['id' => $model->id, 'type' => 'group', 'name' => $model->name],
        };
    }
}
