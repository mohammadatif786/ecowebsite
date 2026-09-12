<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'emoji' => $this->emoji,
            'tagline' => $this->tagline,
            'stripe_product_id' => $this->stripe_product_id,
            'stripe_price_id' => $this->stripe_price_id,
            'price' => $this->price,
            'billing_cycle' => $this->billing_cycle->value,
            'duration_days' => $this->duration_days,
            'status' => $this->status->value,
            'description' => $this->description,
            'features' => $this->features,
            'perks' => $this->perks,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
