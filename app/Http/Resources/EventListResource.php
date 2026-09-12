<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $badgeDate = $this->badgeDate();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'image_url' => $this->image_url,
            'is_free' => (bool) $this->is_free,
            'likes_count' => (int) $this->likes_count,
            'category_name' => $this->whenLoaded('category', fn () => $this->category?->name),
            'is_favourite' => $this->relationLoaded('authUserFavorite')
                ? $this->authUserFavorite !== null
                : false,
            'month' => $badgeDate ? strtoupper($badgeDate->format('M')) : null,
            'day' => $badgeDate?->format('j'),
        ];
    }

    // mirrors EventCard.vue's badgeMonth/badgeDay logic: single events use their
    // one-off date, recurring events use the end of the recurrence window.
    private function badgeDate()
    {
        if (! $this->relationLoaded('eventDetails') || ! $this->eventDetails) {
            return null;
        }

        return match ($this->eventDetails->event_type) {
            'single' => $this->eventDetails->single_event_date,
            'recurring' => $this->eventDetails->recurr_end_date,
            default => null,
        };
    }
}
