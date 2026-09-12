<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'disclaimer' => $this->disclaimer,
            'type' => $this->type,
            'status' => $this->status,
            'venue' => $this->venue,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'latitude' => $this->latitude,
            'longitude' => $this->longtitude,
            'website' => $this->website,
            'phone' => $this->phone,
            'email' => $this->email,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'image_url' => $this->image_url,
            'organizer_id' => $this->organizer_id,
            'organizer_name' => $this->organizer_name,
            'organizer_image_url' => $this->organizer_image_url,
            'is_free' => (bool) $this->is_free,
            'likes_count' => (int) $this->likes_count,
            'average_rating' => (float) $this->average_rating,
            'reviews_count' => (int) $this->reviews_count,
            'category_name' => $this->whenLoaded('category', fn () => $this->category?->name),
            'is_favourite' => $this->relationLoaded('authUserFavorite')
                ? $this->authUserFavorite !== null
                : false,
            'favourites_count' => $this->whenLoaded('favourites', fn () => $this->favourites->count()),
            'event_details' => $this->whenLoaded('eventDetails', fn () => $this->eventDetails
                ? new EventDetailsResource($this->eventDetails)
                : null),
            'reviews' => $this->whenLoaded('approvedReviews', fn () => EventReviewResource::collection($this->approvedReviews)),
        ];
    }
}
